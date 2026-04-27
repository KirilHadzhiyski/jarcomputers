<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessOrder;
use App\Models\CustomerProfile;
use App\Models\Supplier;
use App\Models\Ticket;
use App\Models\User;
use App\Support\AdminBusinessResources;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminBusinessResourceController extends Controller
{
    public function index(Request $request, string $resource): View
    {
        $definition = $this->definition($resource);
        $model = $definition['model'];
        $search = trim((string) $request->query('search', ''));

        $records = $model::query()
            ->when($search !== '', function ($query) use ($definition, $search) {
                $query->where(function ($query) use ($definition, $search) {
                    foreach ($definition['search'] ?? [] as $column) {
                        $query->orWhere($column, 'like', "%{$search}%");
                    }
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.business.index', [
            'definition' => $this->hydrateFields($definition),
            'resource' => $resource,
            'records' => $records,
            'search' => $search,
            'seo' => [
                'title' => $definition['title'],
                'description' => $definition['description'],
            ],
        ]);
    }

    public function create(string $resource): View
    {
        $definition = $this->hydrateFields($this->definition($resource));

        return view('admin.business.form', [
            'definition' => $definition,
            'resource' => $resource,
            'record' => null,
            'seo' => [
                'title' => 'New '.$definition['singular'],
                'description' => $definition['description'],
            ],
        ]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $definition = $this->definition($resource);
        $model = $definition['model'];
        $payload = $this->payload($request, $definition);

        $record = $model::query()->create($payload);

        return redirect()
            ->route('admin.business.edit', [$resource, $record])
            ->with('status', ucfirst($definition['singular']).' created.');
    }

    public function edit(string $resource, int $record): View
    {
        $definition = $this->hydrateFields($this->definition($resource));

        return view('admin.business.form', [
            'definition' => $definition,
            'resource' => $resource,
            'record' => $this->record($definition, $record),
            'seo' => [
                'title' => 'Edit '.$definition['singular'],
                'description' => $definition['description'],
            ],
        ]);
    }

    public function update(Request $request, string $resource, int $record): RedirectResponse
    {
        $definition = $this->definition($resource);
        $model = $this->record($definition, $record);
        $model->update($this->payload($request, $definition, $model));

        return redirect()
            ->route('admin.business.edit', [$resource, $model])
            ->with('status', ucfirst($definition['singular']).' updated.');
    }

    public function destroy(string $resource, int $record): RedirectResponse
    {
        $definition = $this->definition($resource);
        $this->record($definition, $record)->delete();

        return redirect()
            ->route('admin.business.index', $resource)
            ->with('status', ucfirst($definition['singular']).' deleted.');
    }

    private function definition(string $resource): array
    {
        return AdminBusinessResources::get($resource);
    }

    private function record(array $definition, int $id): Model
    {
        return $definition['model']::query()->findOrFail($id);
    }

    private function payload(Request $request, array $definition, ?Model $record = null): array
    {
        $rules = [];

        foreach ($definition['fields'] as $field) {
            $fieldRules = $field['rules'];

            if ($field['unique']) {
                $fieldRules[] = Rule::unique((new $definition['model'])->getTable(), $field['name'])->ignore($record?->getKey());
            }

            $rules[$field['name']] = $fieldRules;
        }

        $validated = $request->validate($rules);

        foreach ($definition['fields'] as $field) {
            if ($field['type'] === 'checkbox') {
                $validated[$field['name']] = $request->boolean($field['name']);
            }

            if (array_key_exists($field['name'], $validated) && $validated[$field['name']] === '') {
                $validated[$field['name']] = null;
            }
        }

        foreach ($definition['fields'] as $field) {
            if ($field['generate'] && blank($validated[$field['name']] ?? null)) {
                $validated[$field['name']] = $this->generateNumber($definition['model'], $field['generate']);
            }

            if ($field['slugFrom'] && blank($validated[$field['name']] ?? null)) {
                $validated[$field['name']] = $this->uniqueSlug(
                    $definition['model'],
                    (string) ($validated[$field['slugFrom']] ?? $definition['singular']),
                    $record,
                );
            }
        }

        return $validated;
    }

    private function hydrateFields(array $definition): array
    {
        foreach ($definition['fields'] as $index => $field) {
            if (($field['dynamic'] ?? null) !== null) {
                $definition['fields'][$index]['options'] = $this->dynamicOptions($field['dynamic']);
            }
        }

        return $definition;
    }

    private function dynamicOptions(string $key): array
    {
        return match ($key) {
            'users' => User::query()->orderBy('name')->get()->mapWithKeys(
                fn (User $user) => [$user->id => "{$user->name} <{$user->email}>"],
            )->all(),
            'tickets' => Ticket::query()->latest()->take(100)->get()->mapWithKeys(
                fn (Ticket $ticket) => [$ticket->id => "#{$ticket->id} {$ticket->subject}"],
            )->all(),
            'customers' => CustomerProfile::query()->orderBy('name')->get()->mapWithKeys(
                fn (CustomerProfile $customer) => [$customer->id => $customer->displayName()],
            )->all(),
            'suppliers' => Supplier::query()->orderBy('name')->pluck('name', 'id')->all(),
            'orders' => BusinessOrder::query()->latest()->take(150)->get()->mapWithKeys(
                fn (BusinessOrder $order) => [$order->id => "{$order->order_number} - {$order->customer_name}"],
            )->all(),
            default => [],
        };
    }

    private function generateNumber(string $modelClass, string $prefix): string
    {
        $nextId = ((int) $modelClass::query()->max('id')) + 1;

        return sprintf('%s-%s-%04d', $prefix, now()->format('Ymd'), $nextId);
    }

    private function uniqueSlug(string $modelClass, string $value, ?Model $record = null): string
    {
        $base = Str::slug($value) ?: 'item';
        $slug = $base;
        $counter = 2;
        $table = (new $modelClass)->getTable();

        while (
            Schema::hasColumn($table, 'slug')
            && $modelClass::query()
                ->where('slug', $slug)
                ->when($record, fn ($query) => $query->whereKeyNot($record->getKey()))
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
