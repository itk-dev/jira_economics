import Vue from 'vue';
import axios from 'axios';

require('./planning.css');

(function () {
    // eslint-disable-next-line no-unused-vars
    const app = new Vue({
        el: '#app',
        data: {
            apiUrl: '',
            hideUsers: {},
            toggle: {},
            sprints: [],
            users: {},
            projects: {},
            numberLoaded: 0,
            projectFilter: '',
            userFilter: ''
        },
        computed: {
            // Sorted by displayName.
            filteredUsers: function () {
                if (this.users === {}) {
                    return [];
                }

                let arr = Object.keys(this.users).map(function (i) {
                    return this.users[i];
                }.bind(this));

                arr = arr.filter(function (item) {
                    return item.displayName.toLowerCase()
                        .indexOf(this.userFilter.toLowerCase()) !== -1;
                }.bind(this));

                arr = arr.sort(function (a, b) {
                    if (a.key === 'unassigned') {
                        return 1;
                    }
                    return (a.displayName.toLocaleLowerCase() > b.displayName.toLocaleLowerCase()) ? 1 : -1;
                });

                return arr;
            },
            // Sorted by name.
            filteredProjects: function () {
                if (this.projects === {}) {
                    return [];
                }

                let arr = Object.keys(this.projects).map(function (i) {
                    return this.projects[i];
                }.bind(this));

                arr = arr.filter(function (item) {
                    return item.name.toLowerCase()
                        .indexOf(this.projectFilter.toLowerCase()) !== -1;
                }.bind(this));

                arr = arr.sort(function (a, b) {
                    return (a.name.toLocaleLowerCase() > b.name.toLocaleLowerCase()) ? 1 : -1;
                });

                return arr;
            }
        },
        created: function () {
            // eslint-disable-next-line no-undef
            this.apiUrl = PLANNING_API_URL;

            // Get hidden users from local storage.
            let hideUsers = window.localStorage.getItem('hideUsers');

            if (hideUsers !== null) {
                this.hideUsers = JSON.parse(hideUsers);
            }

            axios.get(this.apiUrl + '/future_sprints')
                .then(function (response) {
                    this.sprints = response.data.sprints;

                    for (let i = 0; i < this.sprints.length; i++) {
                        this.getSprint(this.sprints[i].id, i);
                    }
                }.bind(this))
                .catch(function (error) {
                    console.log(error);
                });
        },
        methods: {
            toggleUser: function (key) {
                let newValue = !this.hideUsers[key];
                Vue.set(this.hideUsers, key, newValue);
                window.localStorage.setItem('hideUsers', JSON.stringify(this.hideUsers));
            },
            getToggle: function (key) {
                let toggled = this.toggle.hasOwnProperty(key) && this.toggle[key];

                return toggled ? '<i class="fas fa-angle-up"></i>' : '<i class="fas fa-angle-down"></i>';
            },
            keyToggled: function (key) {
                return this.toggle.hasOwnProperty(key) && this.toggle[key];
            },
            toggleKey: function (key) {
                Vue.set(this.toggle, key, !this.toggle[key]);
            },
            getSprintRemainingTotal: function (sprint) {
                let total = 0;

                for (let i in sprint.issues) {
                    let issue = sprint.issues[i];

                    if (issue.timeRemaining &&
                        issue.fields.assignee &&
                        (!this.hideUsers.hasOwnProperty(issue.fields.assignee.key) || this.hideUsers[issue.fields.assignee.key] === false)) {
                        total += issue.timeRemaining;
                    }
                }

                if (total === 0) {
                    return '';
                }

                return total / 3600;
            },
            getRemainingEstimatIssue: function (sprint, issue) {
                if (sprint.hasOwnProperty('issuesById') && sprint.issuesById.hasOwnProperty(issue.id)) {
                    let sprintIssue = sprint.issuesById[issue.id];

                    if (sprintIssue.done) {
                        return 'Done';
                    }

                    if (isNaN(sprintIssue.fields.timetracking.remainingEstimateSeconds)) {
                        return 'UE';
                    }

                    return sprintIssue.fields.timetracking.remainingEstimateSeconds / 3600;
                } else {
                    return '';
                }
            },
            getStatusLevelClass: function (user, sprint) {
                let remainingEstimateUser = this.getRemainingEstimatUser(user, sprint);

                if (remainingEstimateUser > 70) {
                    return 'remaining-critical';
                } else if (remainingEstimateUser == 60) {
                    return 'remaining-warning';
                } else if (remainingEstimateUser > 53 && remainingEstimateUser <= 70) {
                    return 'remaining-danger';
                } else if (remainingEstimateUser > 0 && remainingEstimateUser <= 53) {
                    return 'remaining-success';
                } else {
                    return '';
                }
            },
            getRemainingEstimatUserProjectSprint: function (user, project, sprint) {
                let sum = 0;

                if (user.projects.hasOwnProperty(project.id)) {
                    let issues = user.projects[project.id].issues;
                    for (let issue in issues) {
                        issue = user.projects[project.id].issues[issue];

                        if (issue.sprintId === sprint.id &&
                            (issue.fields.hasOwnProperty('assignee') &&
                                issue.fields.assignee &&
                                user.key ===
                                issue.fields.assignee.key) &&
                            !issue.done &&
                            issue.hasOwnProperty('timeRemaining') &&
                            issue.timeRemaining > 0) {
                            sum += issue.timeRemaining;
                        }
                    }
                }

                if (sum === 0) {
                    return '';
                }

                return (sum / 3600).toFixed(2);
            },
            getRemainingEstimatUser: function (user, sprint) {
                if (user.hasOwnProperty('timeRemaining') && user.timeRemaining.hasOwnProperty(sprint.id)) {
                    return (user.timeRemaining[sprint.id] / 3600).toFixed(2);
                } else {
                    return '';
                }
            },
            getRemainingEstimat: function (project, sprint) {
                if (project.hasOwnProperty('timeRemaining') && project.timeRemaining.hasOwnProperty(sprint.id)) {
                    return (project.timeRemaining[sprint.id] / 3600).toFixed(2);
                } else {
                    return '';
                }
            },
            updateGlobalTable: function (sprint) {
                for (let issue in sprint.issues) {
                    issue = sprint.issues[issue];

                    let assigned = issue.fields.assignee;
                    let project = issue.fields.project;
                    let timeRemaining = issue.fields.timetracking.remainingEstimateSeconds;
                    let issueDone = issue.fields.hasOwnProperty('status') && issue.fields.status.name === 'Done';
                    let saveProject = null;

                    issue.done = issueDone;
                    issue.sprintId = sprint.id;
                    issue.timeRemaining = timeRemaining;

                    // Projects

                    if (this.projects.hasOwnProperty(project.id)) {
                        saveProject = this.projects[project.id];
                    } else {
                        saveProject = project;
                    }

                    if (!saveProject.hasOwnProperty('issues')) {
                        saveProject.issues = [];
                    }

                    saveProject.issues.push(issue);

                    saveProject.open = false;

                    if (!saveProject.hasOwnProperty('timeRemaining')) {
                        saveProject.timeRemaining = {};
                    }

                    if (timeRemaining && !issueDone) {
                        saveProject.timeRemaining[sprint.id] = (saveProject.timeRemaining.hasOwnProperty(sprint.id) ? saveProject.timeRemaining[sprint.id] : 0) + timeRemaining;
                    }

                    if (!saveProject.hasOwnProperty('users')) {
                        saveProject.users = {};
                    }

                    if (!assigned) {
                        saveProject.users['unassigned'] = {
                            displayName: 'Unassigned',
                            key: 'unassigned'
                        };
                        assigned = saveProject.users['unassigned'];
                    } else {
                        if (!saveProject.users.hasOwnProperty(assigned.key)) {
                            saveProject.users[assigned.key] = assigned;
                        }
                    }

                    if (!saveProject.users[assigned.key].hasOwnProperty('issues')) {
                        saveProject.users[assigned.key].issues = {};
                    }

                    saveProject.users[assigned.key].issues[issue.id] = issue;

                    Vue.set(this.projects, saveProject.id, saveProject);

                    // Users

                    let saveUser = null;

                    if (this.users.hasOwnProperty(assigned.key)) {
                        saveUser = this.users[assigned.key];
                    } else {
                        saveUser = assigned;
                    }

                    if (!saveUser.hasOwnProperty('projects')) {
                        saveUser.projects = {};
                    }

                    if (!saveUser.projects.hasOwnProperty(saveProject.id)) {
                        saveUser.projects[saveProject.id] = saveProject;
                    }

                    if (!saveUser.hasOwnProperty('timeRemaining')) {
                        saveUser.timeRemaining = {};
                    }

                    if (timeRemaining && !issueDone) {
                        saveUser.timeRemaining[sprint.id] = (saveUser.timeRemaining.hasOwnProperty(sprint.id) ? saveUser.timeRemaining[sprint.id] : 0) + timeRemaining;
                    }

                    Vue.set(this.users, saveUser.key, saveUser);
                }
            },
            getSprint: function (id, index) {
                axios.get(this.apiUrl + '/issues/' + id)
                    .then(function (response) {
                        let sprint = this.sprints[index];
                        sprint.issues = response.data.issues;
                        sprint.issuesById = {};

                        for (let issue in sprint.issues) {
                            issue = sprint.issues[issue];

                            sprint.issuesById[issue.id] = issue;
                        }

                        Vue.set(this.sprints, index, sprint);

                        this.updateGlobalTable(sprint);
                    }.bind(this))
                    .catch(function (error) {
                        console.log(error);
                    });
            }
        }
    });
})();
