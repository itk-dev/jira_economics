import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PropTypes from 'prop-types';
import 'moment-timezone';
import ReactTable from 'react-table';
import 'react-table/react-table.css';
import moment from 'moment';
import Spinner from './Spinner';
import Button from 'react-bootstrap/Button';

function makeIssueData (jiraIssues) {
    if (jiraIssues.data.data === undefined) {
        return [];
    }
    return jiraIssues.data.data.map((item, i) => ({
        key: `row-${i}`,
        id: item.issueId,
        invoiceEntryId: item.invoiceEntryId ? item.invoiceEntryId : null,
        summary: item.summary,
        created: item.created.date,
        finished: item.finished.date,
        invoiceStatus: '?',
        jiraUsers: item.jiraUsers,
        timeSpent: item.timeSpent ? (item.timeSpent / 3600) : 'N/A'
    }));
}

const searchKeyValue = (data, key, value) => {
    // if falsy or not an object/array return false
    if (!data || typeof data !== 'object') {
        return false;
    }

    // if the value of the key equals value return true
    if (data[key] === value) {
        return true;
    }

    // return the results of using searchKeyValue on all values of the object/array
    return Object.values(data).some((data) => searchKeyValue(data, key, value));
};

class JiraIssues extends Component {
    constructor (props) {
        super(props);
        this.state = {
            selected: [],
            selectAll: 0,
            selectedIssues: {},
            handleJiraIssuesSelected: null
        };

        this.toggleRow = this.toggleRow.bind(this);
        this.handleAccept = this.handleAccept.bind(this);
        this.handleCancel = this.handleCancel.bind(this);
    }

    // @TODO: consider simplifying logic here
    toggleRow (issue) {
        let newSelected = this.state.selected;
        let selectedIssueIndex = -1;
        for (var i = 0; i < newSelected.length; i++) {
            if (newSelected[i] && newSelected[i]['id'] === issue.id) {
                selectedIssueIndex = i;
            }
        }

        if (selectedIssueIndex > -1) {
            newSelected.splice(selectedIssueIndex, 1);
        } else {
            newSelected.push(issue);
        }

        if (newSelected.length === this.props.issueData.length) {
            this.setState({
                selectAll: 1
            });
        } else if (newSelected.length === 0) {
            this.setState({
                selectAll: 0
            });
        } else {
            this.setState({
                selectAll: 2
            });
        }
        this.setState({
            selected: newSelected
        });
    }

    toggleSelectAll () {
        let newSelected = [];

        if (this.state.selectAll === 0) {
            this.props.issueData.forEach(issue => {
                newSelected.push(issue);
            });
        }
        this.setState({
            selected: newSelected,
            selectAll: this.state.selectAll === 0 ? 1 : 0
        });
    }

    createColumns () {
        return [
            {
                id: 'checkbox',
                accessor: '',
                Cell: ({ original }) => {
                    return (
                        <input
                            type="checkbox"
                            className="checkbox"
                            checked={searchKeyValue(this.state.selected, 'id', original.id)}
                            onChange={() => this.toggleRow(original)}
                        />
                    );
                },
                Header: x => {
                    return (
                        <input
                            type="checkbox"
                            className="checkbox"
                            checked={this.state.selectAll === 1}
                            ref={input => {
                                if (input) {
                                    input.indeterminate = this.state.selectAll === 2;
                                }
                            }}
                            onChange={() => this.toggleSelectAll()}
                        />
                    );
                }
            },
            {
                Header: 'Issue',
                accessor: 'summary'
            },
            {
                Header: 'Oprettet',
                id: 'created',
                accessor: d => {
                    return moment(d.created).format('YYYY-MM-DD HH:mm');
                }
            },
            {
                Header: 'Færdiggjort',
                id: 'finished',
                accessor: d => {
                    return moment(d.finished).format('YYYY-MM-DD HH:mm');
                }
            },
            {
                Header: 'Fakturastatus',
                accessor: 'invoiceStatus'
            },
            {
                Header: 'Jirabrugere',
                id: 'jiraUsers',
                accessor: d => {
                    return d.jiraUsers;
                }
            },
            {
                Header: 'Registrerede timer',
                accessor: 'timeSpent'
            }
        ];
    }

    getTimeSpent () {
        if (this.state.selected === undefined) {
            return 0;
        }
        let timeSum = 0;
        this.state.selected.forEach(selectedIssue => {
            if (parseFloat(selectedIssue.timeSpent)) {
                timeSum += selectedIssue.timeSpent;
            }
        });
        return timeSum;
    }

    handleAccept = () => {
        this.props.handleSelectJiraIssues(this.state.selected);
    };

    handleCancel = () => {
        this.props.handleCancelSelectJiraIssues();
    };

    render () {
        if (this.props.jiraIssues.data.data) {
            return (
                <ContentWrapper>
                    <ReactTable
                        data={this.props.issueData}
                        columns={this.createColumns()}
                        defaultPageSize={10}
                        defaultSorted={[{ id: 'issueId', desc: false }]}
                    />
                    <div>{Object.values(this.state.selected).length + ' issue(s) valgt'}</div>
                    <div>{'Total timer valgt: ' + this.getTimeSpent()}</div>

                    <Button variant={'primary'} onClick={this.handleAccept}>Overfør til fakturaindgang.</Button>
                    <Button variant={'warning'} onClick={this.handleCancel}>Annullér</Button>
                </ContentWrapper>
            );
        } else {
            return (
                <ContentWrapper>
                    <Spinner/>
                </ContentWrapper>
            );
        }
    }
}

JiraIssues.propTypes = {
    jiraIssues: PropTypes.object,
    issueData: PropTypes.array,
    selectedIssues: PropTypes.object,
    handleSelectJiraIssues: PropTypes.func.isRequired,
    handleCancelSelectJiraIssues: PropTypes.func.isRequired
};

const mapStateToProps = state => {
    let issueData = makeIssueData(state.jiraIssues);

    return {
        jiraIssues: state.jiraIssues,
        issueData: issueData,
        selectedIssues: state.selectedIssues
    };
};

export default connect(
    mapStateToProps
)(JiraIssues);
