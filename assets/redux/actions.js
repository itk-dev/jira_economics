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
    return fetch(`/api/projects`)
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
    return fetch(`/api/current_user`)
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
    return fetch(`/api/project/${jiraProjectId}`)
      .then(
        response => response.json(),
        dispatch(requestProjectFailure(jiraProjectId, ''))
      )
      .then(json =>
        dispatch(requestProjectSuccess(jiraProjectId, json))
      )
  }
}
