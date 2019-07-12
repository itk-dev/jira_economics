import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import { setSelectedIssues } from '../redux/actions';
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
    id: item.issueId,
    invoiceEntryId: item.invoiceEntryId ? item.invoiceEntryId : null,
    summary: item.summary,
    created: item.created.date,
    finished: item.finished.date,
    invoiceStatus: "?",
    jiraUsers: item.jiraUsers,
    timeSpent: item.timeSpent ? (item.timeSpent / 3600) : "N/A"
  }));
}

const searchKeyValue = (data, key, value) => {
  //if falsy or not an object/array return false
  if (!data || typeof data !== 'object') {
    return false;
  }

  //if the value of the key equals value return true
  if (data[key] === value) {
    return true;
  }

  //return the results of using searchKeyValue on all values of the object/array
  return Object.values(data).some((data) => searchKeyValue(data, key, value));
};

class JiraIssues extends Component {
  constructor(props) {
    super(props);
    this.state = { selected: [], selectAll: 0, selectedIssues: {}, userActions: [] };
    if (this.props.selectedIssues.selectedIssues && this.props.selectedIssues.selectedIssues.length > 0) {
          this.state.selected = this.props.selectedIssues.selectedIssues;
    }
    if (this.props.userActions) {
      this.state.userActions = this.props.userActions;
    }
    this.toggleRow = this.toggleRow.bind(this);
  }

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(rest.actions.getJiraIssues({ id: `${this.props.match.params.projectId}` }));
    if (this.props.location.state && this.props.location.state.existingInvoiceEntryId) {
      this.props.issueData.forEach(issue => {
        if (issue.invoiceEntryId == this.props.location.state.existingInvoiceEntryId) {
          this.toggleRow(issue);
        }
      });
    }
  }

  // @TODO: consider simplifying logic here
  toggleRow(issue) {
    let newSelected = this.state.selected;
    let selectedIssueIndex = -1;
    for (var i = 0; i < newSelected.length; i++) {
      if (newSelected[i] && newSelected[i]['id'] == issue.id) {
        selectedIssueIndex = i;
      }
    }

    if (selectedIssueIndex > -1) {
      newSelected.splice(selectedIssueIndex, 1);
    }
    else {
      newSelected.push(issue);
    }

    if (newSelected.length == this.props.issueData.length) {
      this.setState({
        selectAll: 1
      })
    }
    else if (newSelected.length == 0) {
      this.setState({
        selectAll: 0
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

  handleSubmitIssues = (event) => {
    event.preventDefault();
    const { dispatch } = this.props;
    dispatch(setSelectedIssues(this.state.selected));
    if (this.props.location.state &&
      this.props.location.state.existingInvoiceEntryId) {
      this.props.history.push({
        pathname: `/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}/submit/invoice_entry`,
        state: { from: this.props.location.pathname, existingInvoiceEntryId: this.props.location.state.existingInvoiceEntryId }
      })
    }
    else {
      this.props.history.push({
        pathname: `/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}/submit/invoice_entry`,
        state: { from: this.props.location.pathname }
      });
    }
  }

  handleCancelSubmit = (event) => {
    event.preventDefault();
    const { dispatch } = this.props;
    dispatch(setSelectedIssues({}));
    this.props.history.push(`/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}`);
  }

  getTimeSpent() {
    if (this.state.selected == undefined) {
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

  render() {
    if (this.props.jiraIssues.data.data) {
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
          <div>{Object.values(this.state.selected).length + " issue(s) valgt"}</div>
          <div>{"Total timer valgt: " + this.getTimeSpent()}</div>
          <form id="submitForm" onSubmit={this.handleSubmitIssues}>
            <button type="submit" className="btn btn-primary" id="submit">Fortsæt med valgte issues</button>
          </form>
          <form id="cancelForm" onSubmit={this.handleCancelSubmit}>
            <button type="submit" className="btn btn-danger" id="cancel">Annuller</button>
          </form>
        </ContentWrapper>
      );
    }
    else {
      return (
        <ContentWrapper>
          <div className="spinner-border" style={{ width: '3rem', height: '3rem', role: 'status' }}>
            <span className="sr-only">Loading...</span>
          </div>
        </ContentWrapper>
      );
    }
  }
}

JiraIssues.propTypes = {
  jiraIssues: PropTypes.object,
  issueData: PropTypes.array,
  userActions: PropTypes.object
};

const mapStateToProps = state => {
  let issueData = makeIssueData(state.jiraIssues);

  return {
    jiraIssues: state.jiraIssues,
    issueData: issueData,
    selectedIssues: state.selectedIssues,
    userActions: state.userActions
  };
};

export default connect(
  mapStateToProps
)(JiraIssues);