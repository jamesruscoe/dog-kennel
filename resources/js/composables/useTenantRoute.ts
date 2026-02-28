import { usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import type { SharedProps } from '@/types/kennel';

/**
 * Returns a `route()` wrapper that automatically injects the current company
 * slug into any route that requires a `{company}` parameter.
 *
 * Usage:
 *   const tenantRoute = useTenantRoute()
 *   tenantRoute('staff.dashboard')           // → /pawstay-demo/staff/dashboard
 *   tenantRoute('staff.dogs.show', dog.id)   // → /pawstay-demo/staff/dogs/1
 *   tenantRoute('staff.dogs.show', { dog: 1 }) // same, object form
 */
export function useTenantRoute() {
    const page = usePage<SharedProps>();

    return (name: string, params: Record<string, unknown> | number | string = {}): string => {
        const slug = page.props.company?.slug;

        if (typeof params === 'number' || typeof params === 'string') {
            // Scalar model ID — use array form so Ziggy fills route parameters
            // positionally: {company} first, then the model parameter.
            return route(name, slug ? [slug, params] : [params]);
        }

        return route(name, slug ? { company: slug, ...params } : params);
    };
}
