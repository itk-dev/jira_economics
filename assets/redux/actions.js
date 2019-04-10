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
