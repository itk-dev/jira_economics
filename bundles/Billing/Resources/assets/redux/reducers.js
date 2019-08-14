import { combineReducers } from 'redux';
import rest from './utils/rest';

import { SET_ISSUES } from './actions';

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

const rootReducer = combineReducers({ selectedIssues, ...rest.reducers });

export default rootReducer;
