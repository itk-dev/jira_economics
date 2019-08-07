export const SET_ISSUES = 'SET_ISSUES';
export const CLEAR_INVOICE_ENTRY = 'CLEAR_INVOICE_ENTRY';

export function setSelectedIssues(selectedIssues) {
  return { type: SET_ISSUES, selectedIssues: selectedIssues }
}

export function clearInvoiceEntry(invoiceEntry) {
  return { type: CLEAR_INVOICE_ENTRY, invoiceEntry: invoiceEntry}
}