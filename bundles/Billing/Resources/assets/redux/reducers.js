import { combineReducers } from 'redux';
import rest from "./utils/rest";

import { SET_ISSUES, CLEAR_INVOICE_ENTRY } from './actions';

function selectedIssues (state = {
  selectedIssues: []
}, action) {
  switch (action.type) {
    case SET_ISSUES:
      return Object.assign({}, state, {
        selectedIssues: action.selectedIssues
      });
    default:
      return state;
  }
}

function clearInvoiceEntry (state = {
  invoiceEntry: ''
}, action) {
  switch (action.type) {
    case CLEAR_INVOICE_ENTRY:
      return null;
    default:
      return state;
  }
}

const rootReducer = combineReducers({ clearInvoiceEntry, selectedIssues, ...rest.reducers });

export default rootReducer;
