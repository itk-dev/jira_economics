import { combineReducers } from 'redux';
import rest from "./utils/rest";

const rootReducer = combineReducers({...rest.reducers});

export default rootReducer;
