<?php

namespace App\Services\Integrations;

use App\Models\CustomerReview;
use App\Models\IntegrationConnection;

class GoogleReviewsConnector
{
    public function __construct(private readonly ApiClient $api) {}

    public function sync(IntegrationConnection $connection): int
    {
        $token = $this->api->googleToken($connection->credentials);
        $settings = $connection->settings;
        $url = 'https://mybusiness.googleapis.com/v4/accounts/'.rawurlencode($settings['account_id']).'/locations/'.rawurlencode($settings['location_id']).'/reviews';
        $next = null;
        $count = 0;
        $pages = 0;
        $seenTokens = [];
        do {
            $data = $this->api->json($this->api->request()->withToken($token)->get($url, array_filter(['pageSize' => 50, 'pageToken' => $next])));
            if (! isset($data['totalReviewCount']) || ! is_numeric($data['totalReviewCount']) || (isset($data['reviews']) && ! is_array($data['reviews']))) {
                throw new IntegrationException('Google върна непълен списък с отзиви.');
            }
            foreach ($data['reviews'] ?? [] as $review) {
                $rating = ['ONE' => 1, 'TWO' => 2, 'THREE' => 3, 'FOUR' => 4, 'FIVE' => 5][$review['starRating'] ?? ''] ?? null;
                if (empty($review['reviewId']) || ! $rating) {
                    throw new IntegrationException('Google върна невалиден отзив.');
                }
                $record = CustomerReview::firstOrNew(['source' => 'google', 'external_id' => $review['reviewId']]);
                if (! $record->exists) {
                    $record->is_published = false;
                    $record->is_featured = false;
                }
                $record->fill([
                    'customer_name' => data_get($review, 'reviewer.displayName', 'Google потребител'),
                    'rating' => $rating, 'review_text' => $review['comment'] ?? '',
                    'reviewed_at' => $review['createTime'] ?? null, 'public_reply' => data_get($review, 'reviewReply.comment'),
                ])->save();
                $count++;
            }
            $next = $data['nextPageToken'] ?? null;
            if ($next && (isset($seenTokens[$next]) || ++$pages >= 100)) {
                throw new IntegrationException('Google върна повтаряща се или прекалено дълга пагинация.');
            }
            if ($next) {
                $seenTokens[$next] = true;
            }
        } while ($next);
        $connection->update(['state' => ['rating' => $data['averageRating'] ?? null, 'total_reviews' => $data['totalReviewCount']]]);

        return $count;
    }
}
