export const SET_ISSUES = 'SET_ISSUES';

export function setSelectedIssues(selectedIssues) {
  return { type: SET_ISSUES, selectedIssues: selectedIssues }
}