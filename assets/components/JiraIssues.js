import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import store from '../redux/store';
import { getJiraIssues } from '../redux/actions';
import PropTypes from 'prop-types';
import 'moment-timezone';
import rest from '../redux/utils/rest';
import ReactTable from 'react-table';
import '!style-loader!css-loader!react-table/react-table.css';
import moment from 'moment';

function makeIssueData(jiraIssues) {
  if (jiraIssues.data.data === undefined) {
    return [];
  }
  return jiraIssues.data.data.map((item, i) => ({
    key: `row-${i}`,
    id: item.issue_id,
    summary: item.summary,
    created: item.created.date,
    finished: item.finished.date,
    invoiceStatus: "?",
    jiraUsers: item.jira_users,
    timeSpent: item.time_spent ? (item.time_spent / 3600) : "N/A"
  }));
}

class JiraIssues extends Component {
  constructor(props) {
    super(props);
    this.state = { selected: {}, selectAll: 0 };
    this.toggleRow = this.toggleRow.bind(this);
  }

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(rest.actions.getJiraIssues({ id: `${this.props.params.projectId}` }));
  }

  // @TODO: consider simplifying logic here
  toggleRow(issueId) {
    const newSelected = Object.assign({}, this.state.selected);
    newSelected[issueId] = !this.state.selected[issueId];
    let checked_iter = Object.values(newSelected).values()
    let result = checked_iter.next()
    let checked_num = 0
    while (!result.done) {
      let checked_val = result.value
      if (checked_val === true) {
        checked_num++;
      }
      result = checked_iter.next()
    }

    if (checked_num === this.props.issueData.length) {
      this.setState({
        selectAll: 1
      })
    }
    else {
      this.setState({
        selectAll: 2
      })
    }
    this.setState({
      selected: newSelected
    });
  }

  toggleSelectAll() {
    let newSelected = {};

    if (this.state.selectAll === 0) {
      this.props.issueData.forEach(x => {
        newSelected[x.id] = true;
      });
    }
    this.setState({
      selected: newSelected,
      selectAll: this.state.selectAll === 0 ? 1 : 0
    });
  }

  createColumns() {
    return [
      {
        id: "checkbox",
        accessor: "",
        Cell: ({ original }) => {
          return (
            <input
              type="checkbox"
              className="checkbox"
              checked={this.state.selected[original.id] === true}
              onChange={() => this.toggleRow(original.id)}
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
        Header: "Issue",
        accessor: "summary"
      },
      {
        Header: "Oprettet",
        id: "created",
        accessor: d => {
          return moment(d.created).format("YYYY-MM-DD HH:mm")
        }
      },
      {
        Header: "Færdiggjort",
        id: "finished",
        accessor: d => {
          return moment(d.finished).format("YYYY-MM-DD HH:mm")
        }
      },
      {
        Header: "Fakturastatus",
        accessor: "invoiceStatus"
      },
      {
        Header: "Jirabrugere",
        id: "jiraUsers",
        accessor: d => {
          return d.jiraUsers
        }
      },
      {
        Header: "Registrerede timer",
        accessor: "timeSpent"
      }
    ]
  }

  render() {
    return (
      <ContentWrapper>
        <PageTitle>Jira Issues</PageTitle>
        <div>Vælg issues fra Jira</div>
        <ReactTable
          data={this.props.issueData}
          columns={this.createColumns()}
          defaultPageSize={10}
          defaultSorted={[{ id: "issueId", desc: false }]}
        />
      </ContentWrapper>
    );
  }
}

JiraIssues.propTypes = {
  jiraIssues: PropTypes.object,
  issueData: PropTypes.array
};

const mapStateToProps = state => {
  let issueData = makeIssueData(state.jiraIssues);

  return {
    jiraIssues: state.jiraIssues,
    issueData: issueData
  };
};

export default connect(
  mapStateToProps
)(JiraIssues);