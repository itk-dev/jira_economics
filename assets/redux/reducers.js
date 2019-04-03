import { combineReducers } from 'redux';

import {
  REQUEST_PROJECTS, RECEIVE_PROJECTS,
  REQUEST_CURRENT_USER, RECEIVE_CURRENT_USER,
  REQUEST_PROJECT, REQUEST_PROJECT_FAILURE, REQUEST_PROJECT_SUCCESS,
  REQUEST_INVOICES, REQUEST_INVOICES_FAILURE, REQUEST_INVOICES_SUCCESS,
  REQUEST_INVOICE, REQUEST_INVOICE_FAILURE, REQUEST_INVOICE_SUCCESS,
  REQUEST_INVOICE_ENTRY, REQUEST_INVOICE_ENTRY_FAILURE, REQUEST_INVOICE_ENTRY_SUCCESS,
  REQUEST_INVOICE_ENTRIES, REQUEST_INVOICE_ENTRIES_FAILURE, REQUEST_INVOICE_ENTRIES_SUCCESS,
  UPDATE_INVOICE, UPDATE_INVOICE_FAILURE, UPDATE_INVOICE_SUCCESS
} from './actions';

function projects (state = {
  isFetching: false,
  receivedAt: null,
  projects: []
}, action) {
  switch (action.type) {
  case REQUEST_PROJECTS:
    return Object.assign({}, state, {
      isFetching: true,
      receivedAt: null
    });
  case RECEIVE_PROJECTS:
    return Object.assign({}, state, {
      isFetching: false,
      receivedAt: action.receivedAt,
      projects: action.projects,
    });
  default:
    return state;
  }
}

function invoices (state = {
  isFetching: false,
  receivedAt: null,
  invoices: []
}, action) {
  switch (action.type) {
  case REQUEST_INVOICES:
    return Object.assign({}, state, {
      isFetching: true,
      receivedAt: null
    });
  case REQUEST_INVOICES_FAILURE:
    return state;
  case REQUEST_INVOICES_SUCCESS:
    return Object.assign({}, state, {
      isFetching: false,
      receivedAt: action.receivedAt,
      invoices: action.invoices,
    });
  default:
    return state;
  }
}

function invoiceEntries (state = {
  isFetching: false,
  receivedAt: null,
  invoiceEntries: []
}, action) {
  switch (action.type) {
  case REQUEST_INVOICE_ENTRIES:
    return Object.assign({}, state, {
      isFetching: true,
      receivedAt: null
    });
  case REQUEST_INVOICE_ENTRIES_FAILURE:
    return state;
  case REQUEST_INVOICE_ENTRIES_SUCCESS:
    return Object.assign({}, state, {
      isFetching: false,
      receivedAt: action.receivedAt,
      invoiceEntries: action.invoiceEntries,
    });
  default:
    return state;
  }
}

function currentUser (state = {
  isFetching: false,
  receivedAt: null,
  currentUser: {}
}, action) {
  switch (action.type) {
  case REQUEST_CURRENT_USER:
    return Object.assign({}, state, {
      isFetching: true,
      receivedAt: null
    });
  case RECEIVE_CURRENT_USER:
    return Object.assign({}, state, {
      isFetching: false,
      receivedAt: action.receivedAt,
      currentUser: action.currentUser,
    });
  default:
    return state;
  }
}

function selectedProject (state = {
  isFetching: false,
  receivedAt: null,
  selectedProject: {}
}, action) {
  switch (action.type) {
  case REQUEST_PROJECT:
    return Object.assign({}, state, {
      isFetching: true,
      receivedAt: null
    });
  case REQUEST_PROJECT_FAILURE:
    return state;
  case REQUEST_PROJECT_SUCCESS:
    return Object.assign({}, state, {
      isFetching: false,
      receivedAt: action.receivedAt,
      selectedProject: action.selectedProject,
    });
  default:
    return state;
  }
}

function selectedInvoice (state = {
  isFetching: false,
  receivedAt: null,
  selectedInvoice: {}
}, action) {
  switch (action.type) {
  case REQUEST_INVOICE:
    return Object.assign({}, state, {
      isFetching: true,
      receivedAt: null
    });
  case REQUEST_INVOICE_FAILURE:
    return state;
  case REQUEST_INVOICE_SUCCESS:
    return Object.assign({}, state, {
      isFetching: false,
      receivedAt: action.receivedAt,
      selectedInvoice: action.selectedInvoice,
    });
  default:
    return state;
  }
}

function selectedInvoiceEntry (state = {
  isFetching: false,
  receivedAt: null,
  selectedInvoiceEntry: {}
}, action) {
  switch (action.type) {
  case REQUEST_INVOICE_ENTRY:
    return Object.assign({}, state, {
      isFetching: true,
      receivedAt: null
    });
  case REQUEST_INVOICE_ENTRY_FAILURE:
    return state;
  case REQUEST_INVOICE_ENTRY_SUCCESS:
    return Object.assign({}, state, {
      isFetching: false,
      receivedAt: action.receivedAt,
      selectedInvoiceEntry: action.selectedInvoiceEntry,
    });
  default:
    return state;
  }
}



const rootReducer = combineReducers({
  projects,
  invoices,
  invoiceEntries,
  currentUser,
  selectedProject,
  selectedInvoice,
  selectedInvoiceEntry
});

export default rootReducer;
