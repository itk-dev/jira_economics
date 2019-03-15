import { combineReducers } from 'redux';

import {
  REQUEST_PROJECTS, RECEIVE_PROJECTS,
} from './actions';

function projects (state = {
  isFetching: false,
  projects: []
}, action) {
  switch (action.type) {
  case REQUEST_PROJECTS:
    return Object.assign({}, state, {
      isFetching: true
    });
  case RECEIVE_PROJECTS:
    return Object.assign({}, state, {
      isFetching: false,
      projects: action.projects,
    });
  default:
    return state;
  }
}

const rootReducer = combineReducers({
  projects
});

export default rootReducer;
