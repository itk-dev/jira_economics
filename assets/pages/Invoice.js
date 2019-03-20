import React, { Component } from 'react';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import { BreadcrumbsStateless, BreadcrumbsItem } from '@atlaskit/breadcrumbs';
import PageHeader from '@atlaskit/page-header';
import connect from 'react-redux/es/connect/connect';
import { Link } from 'react-router';
import store from '../redux/store';
import { fetchProject } from '../redux/actions';

const breadcrumbs = (
  <BreadcrumbsStateless onExpand={() => {}}>
    <BreadcrumbsItem text="Some project" key="Some project"/>
  </BreadcrumbsStateless>
);

export class Invoice extends Component {
  componentDidMount() {
    store.dispatch(fetchProject(this.props.params.projectId));
  }

  render () {
    return (
      <ContentWrapper>
        <PageTitle>Billing</PageTitle>
        <PageHeader breadcrumbs={breadcrumbs}>
          Invoices
        </PageHeader>

        <p>Show list of invoices</p>

        <p>{this.props.params.projectId}</p>

        <Link to={`/invoice/${this.props.params.projectId}/entry/1`}>Link til invoice</Link>

      </ContentWrapper>
    );
  }
}

const mapStateToProps = state => {
  return {
    selectedProject: state.selectedProject.selectedProject,
  };
};

export default connect(
  mapStateToProps
)(Invoice);
