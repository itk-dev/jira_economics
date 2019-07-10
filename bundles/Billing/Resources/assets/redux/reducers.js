import { combineReducers } from 'redux';
import rest from "./utils/rest";

import { SET_ISSUES, SET_INVOICE_ENTRIES, ADD_USER_ACTIONS } from './actions';

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

function newInvoiceEntries (state = {
  newInvoiceEntries: []
}, action) {
  switch (action.type) {
    case SET_INVOICE_ENTRIES:
      return Object.assign({}, state, {
        newInvoiceEntries: action.newInvoiceEntries
      });
      default:
        return state;
  }
}

function newUserActions (state = {
  newUserActions: []
}, action) {
  switch (action.type) {
    case ADD_USER_ACTIONS:
      return {
        newUserActions: state.newUserActions.concat(action.newUserActions)
      }
      default:
        return state;
  }
}

const rootReducer = combineReducers({ newUserActions, newInvoiceEntries, selectedIssues, ...rest.reducers });

export default rootReducer;
