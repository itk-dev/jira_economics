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

function makeIssueColumns(jiraIssues) {
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

const columns = [
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

class JiraIssues extends Component {
  constructor(props) {
    super(props);
    this.toggleRow = this.toggleRow.bind(this);
    this.state = { selected: {}, selectAll: 0 };
  }

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(rest.actions.getJiraIssues({ id: `${this.props.params.projectId}` }));
  }

  toggleRow(issueId) {
    const newSelected = Object.assign({}, this.state.selected);
    newSelected[issueId] = !this.state.selected[issueId];
    this.setState({
      selected: newSelected,
      selectAll: 2
    });
  }

  toggleSelectAll() {
    let newSelected = {};

    if (this.state.selectAll === 0) {
      this.props.issueColumns.forEach(x => {
        newSelected[x.issueId] = true;
        console.log("Iterating");
      });
    }
    this.setState({
      selected: newSelected,
      selectAll: this.state.selectAll === 0 ? 1 : 0
    });
  }

  render() {
    return (
      <ContentWrapper>
        <PageTitle>Jira Issues</PageTitle>
        <div>Vælg issues fra Jira</div>
        <ReactTable
          data={this.props.issueColumns}
          columns={columns}
          defaultPageSize={10}
          defaultSorted={[{ id: "issueId", desc: false }]}
        />
      </ContentWrapper>
    );
  }
}

JiraIssues.propTypes = {
  jiraIssues: PropTypes.object,
  issueColumns: PropTypes.array
};

const mapStateToProps = state => {
  let issueColumns = makeIssueColumns(state.jiraIssues);

  return {
    jiraIssues: state.jiraIssues,
    issueColumns: issueColumns
  };
};

export default connect(
  mapStateToProps
)(JiraIssues);