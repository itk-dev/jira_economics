import React, { Component } from 'react';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import connect from 'react-redux/es/connect/connect';
import { Link } from 'react-router';
import store from '../redux/store';
import { fetchProject } from '../redux/actions';
import PropTypes from 'prop-types';
import Spinner from '@atlaskit/spinner';

class Project extends Component {
  componentDidMount() {
    store.dispatch(fetchProject(this.props.params.projectId));
  }

  render () {
    if (this.props.selectedProject.name) {
      return (
        <ContentWrapper>
          <PageTitle>
            {this.props.selectedProject.name + ' (' + this.props.selectedProject.jiraId + ')'}
          </PageTitle>

          <p>Show list of invoices</p>

          <Link
            to={`/project/${this.props.params.projectId}/1`}>Link til invoice</Link>
        </ContentWrapper>
      );
    }
    else {
      return (<ContentWrapper><Spinner size="large"/></ContentWrapper>);
    }
  }
}

Project.propTypes = {
  selectedProject: PropTypes.object,
};

const mapStateToProps = state => {
  return {
    selectedProject: state.selectedProject.selectedProject,
  };
};

export default connect(
  mapStateToProps
)(Project);
