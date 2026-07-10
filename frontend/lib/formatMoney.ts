/**
 * Format a number as money with proper comma separation
 * @param amount - The amount to format
 * @returns Formatted money string
 */
export function formatMoney(amount: number | string): string {
  const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
  
  if (isNaN(numAmount)) {
    return '0';
  }
  
  return numAmount.toLocaleString('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  });
}

/**
 * Format currency with symbol
 * @param amount - The amount to format
 * @param currency - Currency symbol (default: ৳)
 * @returns Formatted currency string
 */
export function formatCurrency(amount: number | string, currency = '৳'): string {
  return `${currency}${formatMoney(amount)}`;
}