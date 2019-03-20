import React, { Component } from 'react';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import { BreadcrumbsStateless, BreadcrumbsItem } from '@atlaskit/breadcrumbs';
import PageHeader from '@atlaskit/page-header';
import connect from 'react-redux/es/connect/connect';
import { Link } from 'react-router';
import store from '../redux/store';
import { fetchProject } from '../redux/actions';
import PropTypes from 'prop-types';
import Spinner from '@atlaskit/spinner';

const breadcrumbs = (
  <BreadcrumbsStateless onExpand={() => {}}>
    <BreadcrumbsItem text="Some project" key="Some project"/>
  </BreadcrumbsStateless>
);

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
          <PageHeader breadcrumbs={breadcrumbs}>
            Invoices
          </PageHeader>

          <p>Show list of invoices</p>

          <p>{this.props.params.projectId}</p>

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
