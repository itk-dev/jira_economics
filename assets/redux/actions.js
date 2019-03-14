import fetch from 'cross-fetch';

// REQUEST_PROJECTS:

export const REQUEST_PROJECTS = 'REQUEST_PROJECTS';

export function requestProjects () {
  return {type: REQUEST_PROJECTS};
}

// REQUEST_PROJECTS:

export const RECEIVE_PROJECTS = 'RECEIVE_PROJECTS';

export function receiveProjects (projects) {
  return {
    type: RECEIVE_PROJECTS,
    projects: projects,
    receivedAt: Date.now()
  };
}

/*
 * Thunk creators.
 */

export function fetchProjects () {
  // Thunk middleware knows how to handle functions.
  // It passes the dispatch method as an argument to the function,
  // thus making it able to dispatch actions itself.

  return function (dispatch) {
    // First dispatch: the app state is updated to inform
    // that the API call is starting.

    dispatch(requestProjects());

    // The function called by the thunk middleware can return a value,
    // that is passed on as the return value of the dispatch method.

    // In this case, we return a promise to wait for.
    // This is not required by thunk middleware, but it is convenient for us.

    return fetch(`/api/projects`)
      .then(
        response => response.json(),
        // Do not use catch, because that will also catch
        // any errors in the dispatch and resulting render,
        // causing a loop of 'Unexpected batch number' errors.
        // https://github.com/facebook/react/issues/6895
        error => console.log('An error occurred.', error)
      )
      .then(projects => {
          // We can dispatch many times!
          // Here, we update the app state with the results of the API call.

          dispatch(receiveProjects(projects));
        }
      );
  };
}
