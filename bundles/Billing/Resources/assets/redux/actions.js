export const SET_ISSUES = 'SET_ISSUES';
export const SET_INVOICE_ENTRIES = 'SET_INVOICE_ENTRIES';
export const ADD_USER_ACTIONS = 'SET_USER_ACTIONS';

export function setSelectedIssues(selectedIssues) {
  return { type: SET_ISSUES, selectedIssues: selectedIssues }
}

export function setInvoiceEntries(newInvoiceEntries) {
  return { type: SET_INVOICE_ENTRIES, newInvoiceEntries: newInvoiceEntries }
}

export function addUserActions(newUserActions) {
  return { type: ADD_USER_ACTIONS, newUserActions: newUserActions }
}