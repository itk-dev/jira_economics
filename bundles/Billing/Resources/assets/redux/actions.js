export const SET_ISSUES = 'SET_ISSUES';

export function setSelectedIssues(selectedIssues) {
  return { type: SET_ISSUES, selectedIssues: selectedIssues }
}

export const SET_INVOICE_ENTRIES = 'SET_INVOICE_ENTRIES';

export function setInvoiceEntries(invoiceEntries) {
  return { type: SET_INVOICE_ENTRIES, invoiceEntries: invoiceEntries }
}