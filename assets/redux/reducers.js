import { combineReducers } from 'redux';

import {
  REQUEST_PROJECTS, RECEIVE_PROJECTS,
  REQUEST_CURRENT_USER, RECEIVE_CURRENT_USER,
  REQUEST_PROJECT, REQUEST_PROJECT_FAILURE, REQUEST_PROJECT_SUCCESS
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

const rootReducer = combineReducers({
  projects,
  currentUser,
  selectedProject
});

export default rootReducer;
