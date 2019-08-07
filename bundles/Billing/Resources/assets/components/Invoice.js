import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import { Link } from 'react-router-dom';
import PropTypes from 'prop-types';
import Moment from 'react-moment';
import 'moment-timezone';
import rest from '../redux/utils/rest';
import ContentFooter from '../components/ContentFooter';
import Form from 'react-bootstrap/Form';
import Button from 'react-bootstrap/Button';
import ButtonGroup from 'react-bootstrap/ButtonGroup';
import Table from 'react-bootstrap/Table';
import ListGroup from 'react-bootstrap/ListGroup';
import Modal from 'react-bootstrap/Modal';

function makePriceData(invoiceEntries, jiraIssues) {
  if (invoiceEntries.data.data === undefined) {
    return [];
  }
  if (jiraIssues.data.data === undefined) {
    return [];
  }
  let priceData = [];
  invoiceEntries.data.data.forEach(invoiceEntry => {
    let key = `row-${invoiceEntry.id}`;
    const amount = invoiceEntry.amount;
    const unitPrice = invoiceEntry.price / amount;
    const totalPrice = invoiceEntry.price;
    priceData[key] = { unitPrice: unitPrice, amount: amount, totalPrice: totalPrice };
  });

  return priceData;
};

class Invoice extends Component {
  constructor(props) {
    super(props);
    this.recordInvoice = this.recordInvoice.bind(this);
    this.deleteInvoice = this.deleteInvoice.bind(this);
    this.handleModalShow = this.handleModalShow.bind(this);
    this.handleModalClose = this.handleModalClose.bind(this);
    this.handleInvoiceDeleteModalShow = this.handleInvoiceDeleteModalShow.bind(this);
    this.handleInvoiceDeleteModalClose = this.handleInvoiceDeleteModalClose.bind(this);
    this.handleInvoiceRecordModalShow = this.handleInvoiceRecordModalShow.bind(this);
    this.handleInvoiceRecordModalClose = this.handleInvoiceRecordModalClose.bind(this);

    this.state = {
       checkedEntries: {},
       showModal: false,
       showDeleteModal: false,
       showRecordModal: false,
       checkedCount: 0,
       invoiceEntries: {},
    };
  };

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(rest.actions.getProject({ id: `${this.props.match.params.projectId}` }));
    dispatch(rest.actions.getJiraIssues({ id: `${this.props.match.params.projectId}` }));
    dispatch(rest.actions.getInvoice({ id: `${this.props.match.params.invoiceId}` }));
    dispatch(rest.actions.getInvoiceEntries({ id: `${this.props.match.params.invoiceId}` }))
    .then((response) => {
      this.setState({ invoiceEntries: response });
    })
    .catch((reason) => console.log('isCanceled', reason.isCanceled));
  };

  recordInvoice = (event) => {
    event.preventDefault();
    this.handleInvoiceRecordModalShow();
  };

  deleteInvoice = (event) => {
    event.preventDefault();
    this.handleInvoiceDeleteModalShow();
  };

  handleAddFromJira = (event) => {
    event.preventDefault();
    this.props.history.push(`/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}/invoice_entry/jira_issues`);
  };

  handleAddManually = (event) => {
    event.preventDefault();
    this.props.history.push({
      pathname: `/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}/submit/invoice_entry`,
      state: { from: this.props.location.pathname }
    });
  };

  // @TODO: show Modal to confirm InvoiceEntry deletion
  handleEntryDelete = (event) => {
    event.preventDefault();
    let checkedInvoiceEntryIds = [];

    for (let [invoiceEntryId, checked] of Object.entries(this.state.checkedEntries)) {
      if (checked) {
        checkedInvoiceEntryIds.push(parseInt(invoiceEntryId));
      }
    }

    this.deleteInvoiceEntries(checkedInvoiceEntryIds);
    this.removeInvoiceEntriesFromState(checkedInvoiceEntryIds);
  };

  async deleteInvoiceEntries(invoiceEntryIds) {
    const { dispatch } = this.props;
    for (let i = 0; i < invoiceEntryIds.length; i++) {
      let result = await dispatch(rest.actions.deleteInvoiceEntry({ id: `${invoiceEntryIds[i]}` }));
    }
  }

  removeInvoiceEntriesFromState(checkedInvoiceEntryIds) {
    if (this.state.invoiceEntries.data && this.state.invoiceEntries.data.length > 0) {
      let filteredInvoiceEntries = this.state.invoiceEntries.data.filter((invoiceEntry) => {
        return !checkedInvoiceEntryIds.includes(invoiceEntry.id);
      });
      let updatedInvoiceEntries = { "data": filteredInvoiceEntries };
      this.setState({ invoiceEntries: updatedInvoiceEntries });
    }

    this.setState({ checkedEntries: {} });
  }

  handleEntryEdit = (event) => {
    event.preventDefault();

    let checkedCount = 0;
    let selectedInvoiceEntryId = false;

    for (let [invoiceEntryId, checked] of Object.entries(this.state.checkedEntries)) {
      if (checked) {
        checkedCount++;
        selectedInvoiceEntryId = invoiceEntryId;
      }
    }

    if (checkedCount !== 1) {
      this.handleModalShow(checkedCount);
      return;
    }

    let invoiceEntry = {};

    // Get the selected InvoiceEntry
    if (this.props.invoiceEntries.data && this.props.invoiceEntries.data.data) {
      invoiceEntry = this.props.invoiceEntries.data.data.filter(obj => {
        return obj.id == selectedInvoiceEntryId;
      }).pop();
    }

    // InvoiceEntry with Jira issues?
    if (invoiceEntry.jiraIssueIds) {
      this.props.history.push({
        pathname: `/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}/invoice_entry/jira_issues`,
        state: { existingInvoiceEntryId: selectedInvoiceEntryId }
      });
    }
    // InvoiceEntry without Jira issues
    else {
      this.props.history.push({
        pathname: `/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}/submit/invoice_entry`,
        state: { from: this.props.location.pathname, existingInvoiceEntryId: selectedInvoiceEntryId }
      });
    }
  };

  handleCheckboxChange = (event) => {
    const target = event.target;
    let stateCopy = Object.assign({}, this.state);
    stateCopy.checkedEntries[target.id] = target.checked;
    this.setState(stateCopy);
  };

  getPriceData(invoiceEntryId, key) {
    if (this.props.priceData[`row-${invoiceEntryId}`] && this.props.priceData[`row-${invoiceEntryId}`][key]) {
      return this.props.priceData[`row-${invoiceEntryId}`][key];
    }
    else {
      return 0;
    }
  };

  handleModalClose() {
    this.setState({ showModal: false });
  };

  handleModalShow(checkedCount) {
    this.setState({ showModal: true, checkedCount: checkedCount });
  };

  handleInvoiceDeleteModalShow() {
    this.setState({ showDeleteModal: true });
  };

  handleInvoiceDeleteModalClose = (event) => {
    event.preventDefault();
    if (event.target.id == "delete-invoice-btn") {
      const { dispatch } = this.props;
      dispatch(rest.actions.deleteInvoice({ id: `${this.props.match.params.invoiceId}` }));
      // @TODO: Check that deletion is successful before navigating back to main billing page
      this.props.history.push(`/`);
    }
    this.setState({ showDeleteModal: false });
  };

  handleInvoiceRecordModalShow() {
    this.setState({ showRecordModal: true });
  };

  handleInvoiceRecordModalClose = (event) => {
    event.preventDefault();
    if (event.target.id = "record-invoice-btn") {
      const { dispatch } = this.props;
      const id = this.props.match.params.invoiceId;
      const name = this.props.invoice.data.name;
      const recorded = true;
      const created = this.props.createdAt;
      const invoiceData = {
        id,
        name,
        recorded,
        created
      };
      dispatch(rest.actions.updateInvoice({ id: `${this.props.match.params.invoiceId}` }, {
        body: JSON.stringify(invoiceData)
      }));
    }
    this.setState({ showRecordModal: false });
  };

  // @TODO: show spinner while invoiceEntries are being loaded
  render() {
    if (this.props.invoice.data.jiraId && this.props.invoice.data.jiraId != this.props.match.params.projectId) {
      return (
        <ContentWrapper>
          <PageTitle>Invoice</PageTitle>
          <div>Error: the requested invoice does not match the project specified in the URL</div>
          <div>(URL contains projectId '{this.props.match.params.projectId}'
           but invoice with id '{this.props.match.params.invoiceId}'
            belongs to project with id '{this.props.invoice.data.jiraId}')
          </div>
        </ContentWrapper>
      );
    }
    else if (this.props.project.data.name) {
      return (
        <ContentWrapper>
          <PageTitle breadcrumb={"Invoice for project [" + this.props.project.data.name + "] (" + this.props.match.params.projectId + ")"}>
            {this.props.invoice.data.name && this.props.invoice.data.name}
          </PageTitle>
          <div className="row">
            <div className="col-md-4">
              <p>
                Invoice number: <strong className="pr-3">{this.props.match.params.invoiceId}</strong>
                Invoice recorded: <strong>{String(this.props.invoice.data.recorded)}</strong>
              </p>
              <p>Invoice description TODO: save with invoice data</p>
            </div>
            <div className="col-md-8 text-right">
              <ButtonGroup aria-label="Invoice actions">
                <Button variant="primary" type="submit" id="record-invoice" className="mr-3" onClick={this.recordInvoice}>
                  Record invoice
                </Button>
                <Button variant="danger" type="submit" id="delete" className="mr-3" onClick={this.deleteInvoice}>
                  Delete invoice
                </Button>
              </ButtonGroup>
            </div>
          </div>
          <div className="row">
            <div className="col-md-8">
              <h2>Invoice entries</h2>
              <div className="row mb-3">
                <div className="col-md-6">
                  <Button variant="outline-success" type="submit" className="mr-3" onClick={this.handleAddFromJira}>Add entry from Jira</Button>
                  <Button variant="outline-success" type="submit" onClick={this.handleAddManually}>Add manual entry</Button>
                </div>
                <div className="col-md-6 text-right">
                  <ButtonGroup aria-label="Entry actions">
                    <Button variant="primary" type="submit" id="editEntry" onClick={this.handleEntryEdit}>
                      Edit entry
                    </Button>
                    <Button variant="danger" type="submit" id="deleteEntry" onClick={this.handleEntryDelete}>
                      Delete entry
                    </Button>
                  </ButtonGroup>
                </div>
              </div>
              <Table responsive hover className="table-borderless bg-light">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Account number</th>
                    <th>Item name</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Item price (DKK)</th>
                    <th>Total price (DKK)</th>
                  </tr>
                </thead>
                <tbody>
                  {this.state.invoiceEntries.data && this.state.invoiceEntries.data.map((item) =>
                    <tr key={item.id}>
                      <td><Form.Check aria-label="" id={item.id} onChange={this.handleCheckboxChange} /></td>
                      <td>{item.account}</td>
                      <td><Link to={`/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}
                      /${item.id}`}>{item.product}</Link></td>
                      <td>{item.description}</td>
                      <td>{this.getPriceData(item.id, 'amount')}</td>
                      <td>{this.getPriceData(item.id, 'unitPrice')}</td>
                      <td>{this.getPriceData(item.id, 'totalPrice')}</td>
                    </tr>
                  )}
                </tbody>
              </Table>
            </div>
            <div className="col-md-4">
              <h4>Client information</h4>
              <ListGroup>
                <ListGroup.Item><span className="text-muted d-inline-block w-25">Name</span> Customer name</ListGroup.Item>
                <ListGroup.Item><span className="text-muted d-inline-block w-25">Contact</span> Customer contact</ListGroup.Item>
                <ListGroup.Item><span className="text-muted d-inline-block w-25">CVR</span> XXXXXXXX</ListGroup.Item>
                <ListGroup.Item><span className="text-muted d-inline-block w-25">EAN</span> XXXXXXXXXX</ListGroup.Item>
              </ListGroup>
            </div>
          </div>
          <ContentFooter>
            Invoice created <strong><Moment format="YYYY-MM-DD HH:mm">{this.props.createdAt}</Moment></strong>
          </ContentFooter>
          <Modal show={this.state.showModal} onHide={this.handleModalClose}>
            <Modal.Header>
              <Modal.Title>Error</Modal.Title>
            </Modal.Header>
            {this.state.checkedCount > 1 &&
              <Modal.Body>Cannot edit more than one InvoiceEntry at a time!</Modal.Body>
            }
            {this.state.checkedCount == 0 &&
              <Modal.Body>Please select an InvoiceEntry for editing</Modal.Body>
            }
            <Modal.Footer>
              <Button variant="secondary" onClick={this.handleModalClose}>
                Ok
            </Button>
            </Modal.Footer>
          </Modal>
          <Modal show={this.state.showDeleteModal} onHide={this.handleInvoiceDeleteModalClose}>
          <Modal.Header>
            <Modal.Title>Confirm deletion</Modal.Title>
          </Modal.Header>
          <Modal.Body>Are you sure you want to delete this invoice?</Modal.Body>
          <Modal.Footer>
            <Button variant="secondary" onClick={this.handleInvoiceDeleteModalClose}>
              Cancel
            </Button>
            <Button id="delete-invoice-btn" variant="danger" onClick={this.handleInvoiceDeleteModalClose}>
              Delete
            </Button>
          </Modal.Footer>
        </Modal>
        <Modal show={this.state.showRecordModal} onHide={this.handleInvoiceRecordModalClose}>
          <Modal.Header>
            <Modal.Title>Confirm recording</Modal.Title>
          </Modal.Header>
          <Modal.Body>Are you sure you want to record this invoice?</Modal.Body>
          <Modal.Footer>
            <Button variant="secondary" onClick={this.handleInvoiceRecordModalClose}>
              Cancel
            </Button>
            <Button id="record-invoice-btn" variant="primary" onClick={this.handleInvoiceRecordModalClose}>
              Record
            </Button>
          </Modal.Footer>
        </Modal>
        </ContentWrapper>
      );
    }
    else {
      return (
        <ContentWrapper>
          <div className="spinner-border"
            style={{ width: '3rem', height: '3rem', role: 'status' }}>
            <span className="sr-only">Loading...</span>
          </div>
        </ContentWrapper>
      );
    }
  }
}

Invoice.propTypes = {
  invoice: PropTypes.object,
  createdAt: PropTypes.string,
  invoiceEntries: PropTypes.object,
  jiraIssues: PropTypes.object,
  project: PropTypes.object,
  dispatch: PropTypes.func.isRequired
};

const mapStateToProps = state => {
  let priceData = makePriceData(state.invoiceEntries, state.jiraIssues);
  let createdAt = '';
  if (state.invoice.data.created && state.invoice.data.created.date) {
    createdAt = state.invoice.data.created.date;
  }

  return {
    invoice: state.invoice,
    createdAt: createdAt,
    invoiceEntries: state.invoiceEntries,
    jiraIssues: state.jiraIssues,
    project: state.project,
    priceData: priceData
  };
};

export default connect(
  mapStateToProps
)(Invoice);
