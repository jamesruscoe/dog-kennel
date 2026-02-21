import axios from 'axios';

/**
 * Non-Inertia API calls for kennel settings data.
 * Used by the calendar store and capacity checks.
 */
export const KennelSettingsService = {
    async getOccupancy(year: number, month: number) {
        const { data } = await axios.get('/staff/calendar/occupancy', {
            params: { year, month },
        });
        return data as { occupancy: Record<string, number>; max_capacity: number };
    },
};
