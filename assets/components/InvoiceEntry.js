import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import store from '../redux/store';
import { fetchInvoiceEntry } from '../redux/actions';
import PropTypes from 'prop-types';
import Spinner from '@atlaskit/spinner';
import rest from '../redux/utils/rest';

export class InvoiceEntry extends Component {
  componentDidMount() {
    const {dispatch} = this.props;
    dispatch(rest.actions.getInvoiceEntry({id: `${this.props.params.invoiceEntryId}`}));
  }

  render () {
    if (this.props.invoiceEntry.data.name) {
      return (
        <ContentWrapper>
          <PageTitle>Invoice Entry</PageTitle>
          <div>ProjectID: {this.props.params.projectId}</div>
          <div>InvoiceID: {this.props.params.invoiceId}</div>
          <div>InvoiceEntryID: {this.props.params.invoiceEntryId}</div>
          <div>InvoiceEntryName: {this.props.invoiceEntry.data.name}</div>
        </ContentWrapper>
      );
    }
    else {
      return (<ContentWrapper><Spinner size="large"/></ContentWrapper>);
    }
  }
}

InvoiceEntry.propTypes = {
  invoiceEntry: PropTypes.object
};

const mapStateToProps = state => {
  return {
    invoiceEntry: state.invoiceEntry
  };
};

export default connect(
  mapStateToProps
)(InvoiceEntry);
