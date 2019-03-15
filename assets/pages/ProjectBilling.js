import React, { Component } from 'react';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';

export default class ProjectBilling extends Component {
  render () {
    return (
      <ContentWrapper>
        <PageTitle>ProjectBilling</PageTitle>
        <p>{this.props.params.projectId}</p>
      </ContentWrapper>
    );
  }
}
