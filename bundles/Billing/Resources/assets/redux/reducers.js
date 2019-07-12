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

function userActions (state = {
  userActions: []
}, action) {
  switch (action.type) {
    case ADD_USER_ACTIONS:
      return {
        userActions: state.userActions.concat(action.userActions)
      }
      default:
        return state;
  }
}

const rootReducer = combineReducers({ userActions, newInvoiceEntries, selectedIssues, ...rest.reducers });

export default rootReducer;
