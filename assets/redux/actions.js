import fetch from 'cross-fetch';

// PROJECTS:

export const REQUEST_PROJECTS = 'REQUEST_PROJECTS';
export function requestProjects () {
  return {type: REQUEST_PROJECTS};
}

export const RECEIVE_PROJECTS = 'RECEIVE_PROJECTS';
export function receiveProjects (projects) {
  return {
    type: RECEIVE_PROJECTS,
    projects: projects,
    receivedAt: Date.now()
  };
}

function shouldFetchProjects(state) {
  const projects = state.projects;

  if (!projects) {
    return true;
  } else if (projects.isFetching) {
    return false;
  } else if (projects.receivedAt === null) {
    return true;
  } else {
    return projects.receivedAt + 5 * 60 * 1000 < Date.now()
  }
}

export function fetchProjects () {
  return function (dispatch) {
    dispatch(requestProjects());

    // The function called by the thunk middleware can return a value,
    // that is passed on as the return value of the dispatch method.

    // In this case, we return a promise to wait for.
    // This is not required by thunk middleware, but it is convenient for us.

    return fetch(`/jira_api/projects`)
      .then(
        response => response.json(),
        error => console.log('An error occurred.', error)
      )
      .then(projects => {
        dispatch(receiveProjects(projects));
      });
  };
}

export function fetchProjectsIfNeeded() {
  return (dispatch, getState) => {
    if (shouldFetchProjects(getState())) {
      return dispatch(fetchProjects())
    }
  }
}

// INVOICES:

export const REQUEST_INVOICES = 'REQUEST_INVOICES';
export function requestInvoices () {
  return {type: REQUEST_INVOICES};
}

export const REQUEST_INVOICES_FAILURE = 'REQUEST_INVOICES_FAILURE';
export function requestInvoicesFailure (err) {
  return {
    type: REQUEST_INVOICES_FAILURE,
    error: err
  };
}
export const REQUEST_INVOICES_SUCCESS = 'REQUEST_INVOICES_SUCCESS';
export function requestInvoicesSuccess (jiraProjectId, invoices) {
  return {
    type: REQUEST_INVOICES_SUCCESS,
    receivedAt: Date.now(),
    jiraProjectId: jiraProjectId,
    invoices: invoices
  };
}

export function fetchInvoices(jiraProjectId) {
  return function(dispatch) {
    dispatch(requestInvoices(jiraProjectId));
    return fetch(`/jira_api/invoices/${jiraProjectId}`)
      .then(
        response => response.json(),
        error => dispatch(requestInvoicesFailure(jiraProjectId, error))
      )
      .then(
        json => dispatch(requestInvoicesSuccess(jiraProjectId, json))
      )
  }
}

// INVOICE_ENTRIES:

export const REQUEST_INVOICE_ENTRIES = 'REQUEST_INVOICE_ENTRIES';
export function requestInvoiceEntries () {
  return {type: REQUEST_INVOICE_ENTRIES};
}

export const REQUEST_INVOICE_ENTRIES_FAILURE = 'REQUEST_INVOICE_ENTRIES_FAILURE';
export function requestInvoiceEntriesFailure (err) {
  return {
    type: REQUEST_INVOICE_ENTRIES_FAILURE,
    error: err
  };
}
export const REQUEST_INVOICE_ENTRIES_SUCCESS = 'REQUEST_INVOICE_ENTRIES_SUCCESS';
export function requestInvoiceEntriesSuccess (invoiceId, invoiceEntries) {
  return {
    type: REQUEST_INVOICE_ENTRIES_SUCCESS,
    receivedAt: Date.now(),
    invoiceId: invoiceId,
    invoiceEntries: invoiceEntries
  };
}

export function fetchInvoiceEntries(invoiceId) {
  return function(dispatch) {
    dispatch(requestInvoiceEntries(invoiceId));
    return fetch(`/jira_api/invoice_entries/${invoiceId}`)
      .then(
        response => response.json(),
        error => dispatch(requestInvoiceEntriesFailure(invoiceId, error))
      )
      .then(
        json => dispatch(requestInvoiceEntriesSuccess(invoiceId, json))
      )
  }
}

// CURRENT_USER:

export const REQUEST_CURRENT_USER = 'REQUEST_CURRENT_USER';
export function requestCurrentUser () {
  return {type: REQUEST_CURRENT_USER};
}

export const RECEIVE_CURRENT_USER = 'RECEIVE_CURRENT_USER';
export function receiveCurrentUser (currentUser) {
  return {
    type: RECEIVE_CURRENT_USER,
    currentUser: currentUser,
    receivedAt: Date.now()
  };
}

function shouldFetchCurrentUser(state) {
  const currentUser = state.currentUser;

  if (!currentUser) {
    return true;
  } else if (currentUser.isFetching) {
    return false;
  } else if (currentUser.receivedAt === null) {
    return true;
  } else {
    return currentUser.receivedAt + 5 * 60 * 1000 < Date.now()
  }
}

export function fetchCurrentUser () {
  return function (dispatch) {
    dispatch(requestCurrentUser());
    return fetch(`/jira_api/current_user`)
      .then(
        response => response.json(),
        error => console.log('An error occurred.', error)
      )
      .then(currentUser => {
        dispatch(receiveCurrentUser(currentUser));
      });
  };
}

export function fetchCurrentUserIfNeeded() {
  return (dispatch, getState) => {
    if (shouldFetchCurrentUser(getState())) {
      return dispatch(fetchCurrentUser())
    }
  }
}


// PROJECT:

export const REQUEST_PROJECT = 'REQUEST_PROJECT';
export function requestProject () {
  return {type: REQUEST_PROJECT};
}
export const REQUEST_PROJECT_FAILURE = 'REQUEST_PROJECT_FAILURE';
export function requestProjectFailure (err) {
  return {
    type: REQUEST_PROJECT_FAILURE,
    error: err
  };
}
export const REQUEST_PROJECT_SUCCESS = 'REQUEST_PROJECT_SUCCESS';
export function requestProjectSuccess (jiraProjectId, selectedProject) {
  return {
    type: REQUEST_PROJECT_SUCCESS,
    receivedAt: Date.now(),
    jiraProjectId: jiraProjectId,
    selectedProject: selectedProject
  };
}

export function fetchProject(jiraProjectId) {
  return function(dispatch) {
    dispatch(requestProject(jiraProjectId));
    return fetch(`/jira_api/project/${jiraProjectId}`)
      .then(
        response => response.json(),
        error => dispatch(requestProjectFailure(jiraProjectId, error))
      )
      .then(
        json => dispatch(requestProjectSuccess(jiraProjectId, json))
      )
  }
}

// INVOICE:

export const REQUEST_INVOICE = 'REQUEST_INVOICE';
export function requestInvoice () {
  return {type: REQUEST_INVOICE};
}
export const REQUEST_INVOICE_FAILURE = 'REQUEST_INVOICE_FAILURE';
export function requestInvoiceFailure (err) {
  return {
    type: REQUEST_INVOICE_FAILURE,
    error: err
  };
}
export const REQUEST_INVOICE_SUCCESS = 'REQUEST_INVOICE_SUCCESS';
export function requestInvoiceSuccess (invoiceId, selectedInvoice) {
  return {
    type: REQUEST_INVOICE_SUCCESS,
    receivedAt: Date.now(),
    invoiceId: invoiceId,
    selectedInvoice: selectedInvoice
  };
}

export function fetchInvoice(invoiceId) {
  return function(dispatch) {
    dispatch(requestInvoice(invoiceId));
    return fetch(`/jira_api/invoice/${invoiceId}`)
      .then(
        response => response.json(),
        error => dispatch(requestInvoiceFailure(invoiceId, error))
      )
      .then(
        json => dispatch(requestInvoiceSuccess(invoiceId, json))
      )
  }
}

export const UPDATE_INVOICE = 'UPDATE_INVOICE';
export function updateInvoice() {
  return {type: UPDATE_INVOICE};
}
export const UPDATE_INVOICE_FAILURE = 'UPDATE_INVOICE_FAILURE';
export function updateInvoiceFailure (err) {
  return {
    type: UPDATE_INVOICE_FAILURE,
    error: err
  };
}
export const UPDATE_INVOICE_SUCCESS = 'UPDATE_INVOICE_SUCCESS';
export function updateInvoiceSuccess (invoiceData, json) {
  return {
    type: UPDATE_INVOICE_SUCCESS,
    receivedAt: Date.now(),
    invoiceId: invoiceData.id,
    selectedInvoice: json
  };
}

export function editInvoice(invoiceData) {
  return function(dispatch) {
    dispatch(updateInvoice(invoiceData));
    return fetch(`/jira_api/invoice/${invoiceData.id}`, {
      method: 'PUT',
      body: JSON.stringify(invoiceData)
    })
    .then(
      response => response.json(),
      error => dispatch(updateInvoiceFailure(invoiceData, error))
    )
    .then(
      json => dispatch(updateInvoiceSuccess(invoiceData, json))
    )
  }
}

// INVOICE_ENTRY:

export const REQUEST_INVOICE_ENTRY = 'REQUEST_INVOICE_ENTRY';
export function requestInvoiceEntry () {
  return {type: REQUEST_INVOICE_ENTRY}
}
export const REQUEST_INVOICE_ENTRY_FAILURE = 'REQUEST_INVOICE_ENTRY_FAILURE';
export function requestInvoiceEntryFailure (err) {
  return {
    type: REQUEST_INVOICE_ENTRY_FAILURE,
    error: err
  };
}
export const REQUEST_INVOICE_ENTRY_SUCCESS = 'REQUEST_INVOICE_ENTRY_SUCCESS';
export function requestInvoiceEntrySuccess (invoiceEntryId, selectedInvoiceEntry) {
  return {
    type: REQUEST_INVOICE_ENTRY_SUCCESS,
    receivedAt: Date.now(),
    invoiceEntryId: invoiceEntryId,
    selectedInvoiceEntry: selectedInvoiceEntry
  };
}

export function fetchInvoiceEntry(invoiceEntryId) {
  return function(dispatch) {
    dispatch(requestInvoiceEntry(invoiceEntryId));
    return fetch(`/jira_api/invoice_entry/${invoiceEntryId}`)
      .then(
        response => response.json(),
        error => dispatch(requestInvoiceEntryFailure(invoiceEntryId, error))
      )
      .then(
        json => dispatch(requestInvoiceEntrySuccess(invoiceEntryId, json))
      )
  }
}