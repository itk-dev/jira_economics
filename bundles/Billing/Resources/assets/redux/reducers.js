import { combineReducers } from 'redux';
import rest from "./utils/rest";

import { SET_ISSUES, SET_INVOICE_ENTRIES } from './actions';

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

function invoiceEntries (state = {
  invoiceEntries: []
}, action) {
  switch (action.type) {
    case SET_INVOICE_ENTRIES:
      return Object.assign({}, state, {
        invoiceEntries: action.invoiceEntries
      });
      default:
        return state;
  }
}

const rootReducer = combineReducers({ selectedIssues, ...rest.reducers });

export default rootReducer;
