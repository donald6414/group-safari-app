/**
 * Counts the number of days between a start date and end date
 * @param startDate - The start date (Date object, string in YYYY-MM-DD format, or timestamp)
 * @param endDate - The end date (Date object, string in YYYY-MM-DD format, or timestamp)
 * @returns The number of days between the two dates (inclusive of both dates)
 */
export function countNumberOfDays(
    startDate: Date | string | number,
    endDate: Date | string | number
): number {
    // Convert both dates to Date objects
    const start = typeof startDate === 'string' || typeof startDate === 'number'
        ? new Date(startDate)
        : startDate;
    
    const end = typeof endDate === 'string' || typeof endDate === 'number'
        ? new Date(endDate)
        : endDate;
    
    // Normalize dates to midnight to avoid timezone issues
    const startNormalized = new Date(start.getFullYear(), start.getMonth(), start.getDate());
    const endNormalized = new Date(end.getFullYear(), end.getMonth(), end.getDate());
    
    // Calculate the difference in milliseconds
    const diffInMs = endNormalized.getTime() - startNormalized.getTime();
    
    // Convert to days and add 1 to include both start and end dates
    const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24)) + 1;
    
    return diffInDays;
}

