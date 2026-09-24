export async function adminApi<T>(
    path: string,
    method = "GET",
    data?: unknown,
): Promise<T> {
    const response = await fetch(`/admin/workspace/${path}`, {
        method,
        credentials: "same-origin",
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            "X-CSRF-TOKEN":
                document.querySelector<HTMLMetaElement>(
                    'meta[name="csrf-token"]',
                )?.content ?? "",
        },
        ...(data === undefined ? {} : { body: JSON.stringify(data) }),
    });
    if (response.status === 401 || response.status === 419) {
        window.location.assign("/login");
        throw new Error("Сесията изтече. Влезте отново.");
    }
    const result = await response.json();
    if (!response.ok) {
        const errors = Object.values(result.errors ?? {})
            .flat()
            .join(" ");
        throw new Error(
            errors || "Действието не завърши успешно. Опитайте отново.",
        );
    }
    return result as T;
}
