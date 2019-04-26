import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import store from '../redux/store';
import { getJiraIssues } from '../redux/actions';
import PropTypes from 'prop-types';
import Moment from 'react-moment';
import 'moment-timezone';
import rest from '../redux/utils/rest';

class JiraIssues extends Component {
  constructor(props) {
    super(props);
  }
  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(rest.actions.getJiraIssues({ id: `${this.props.params.projectId}` }));
  }
  render() {
    return (
      // @TODO: Cleanup redundancy in if/else
      <ContentWrapper>
        <PageTitle>Jira Issues</PageTitle>
        <div>Vælg issues fra Jira</div>
        <table>
          <thead>
            <tr>
              <td>Issue</td><td>Oprettet</td><td>Done</td><td>Fakturastatus</td><td>Jira brugere</td><td>Registrerede timer</td>
            </tr>
          </thead>
          <tbody>
            {this.props.jiraIssues.data.data && this.props.jiraIssues.data.data.map(function (item, i) {
              if (item.time_spent != null) {
                return <tr key={i}>
                  <td>{item.summary}</td>
                  <td><Moment format="YYYY-MM-DD HH:mm">{item.created.date}</Moment></td>
                  <td><Moment format="YYYY-MM-DD HH:mm">{item.finished.date}</Moment></td>
                  <td>?</td>
                  <td>Users go here...</td>
                  <td>{item.time_spent / 3600}</td>
                </tr>;
              }
              else {
                return <tr key={i}>
                  <td>{item.summary}</td>
                  <td><Moment format="YYYY-MM-DD HH:mm">{item.created.date}</Moment></td>
                  <td><Moment format="YYYY-MM-DD HH:mm">{item.finished.date}</Moment></td>
                  <td>?</td>
                  <td>Users go here...</td>
                  <td>N/A</td>
                </tr>;
              }
            })}
          </tbody>
        </table>
      </ContentWrapper>
    );
  }
  //{item.jira_users}
}

JiraIssues.propTypes = {
  jiraIssues: PropTypes.object
};

const mapStateToProps = state => {
  return {
    jiraIssues: state.jiraIssues
  };
};

export default connect(
  mapStateToProps
)(JiraIssues);