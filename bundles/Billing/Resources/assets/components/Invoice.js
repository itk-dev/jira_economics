import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import { Link } from 'react-router-dom';
import PropTypes from 'prop-types';
import Moment from 'react-moment';
import 'moment-timezone';
import rest from '../redux/utils/rest';
import { push } from 'react-router-redux';
import ContentFooter from '../components/ContentFooter';
import Form from 'react-bootstrap/Form';
import Button from 'react-bootstrap/Button';
import ButtonGroup from 'react-bootstrap/ButtonGroup';
import Table from 'react-bootstrap/Table';
import ListGroup from 'react-bootstrap/ListGroup';
import Alert from 'react-bootstrap/Alert';
import Spinner from 'react-bootstrap/Spinner';
import Row from 'react-bootstrap/Row';
import Col from 'react-bootstrap/Col';
import InputGroup from 'react-bootstrap/InputGroup';

class Invoice extends Component {
  constructor (props) {
    super(props);

    this.handleRecordSubmit = this.handleRecordSubmit.bind(this);
    this.handleEditSubmit = this.handleEditSubmit.bind(this);
    this.state = {invoiceEntryName: ''};
  }

  componentDidMount () {
    const {dispatch} = this.props;
    dispatch(rest.actions.getInvoice({id: `${this.props.match.params.invoiceId}`}));
    dispatch(rest.actions.getInvoiceEntries({id: `${this.props.match.params.invoiceId}`}));
  }

  // @TODO: consider cleaning up redundancy
  handleEditSubmit = (event) => {
    event.preventDefault();
    const {dispatch} = this.props;
    const id = this.props.match.params.invoiceId;
    // @TODO: look into getting this value from the event instead
    const name = $('#edit-invoice-entry-name').val();
    const recorded = this.props.invoice.data.recorded;
    const created = this.props.createdAt;
    const invoiceData = {
      id,
      name,
      recorded,
      created
    };
    dispatch(rest.actions.updateInvoice({id: `${this.props.match.params.invoiceId}`}, {
      body: JSON.stringify(invoiceData)
    }));
  };

  handleRecordSubmit = (event) => {
    event.preventDefault();
    const {dispatch} = this.props;
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
    dispatch(rest.actions.updateInvoice({id: `${this.props.match.params.invoiceId}`}, {
      body: JSON.stringify(invoiceData)
    }));
  };

  handleCreateSubmit = (event) => {
    event.preventDefault();
    const {dispatch} = this.props;
    const invoiceId = this.props.match.params.invoiceId;
    // @TODO: look into getting this value from the event instead
    const name = $('#invoice-entry-name').val();
    // @TODO: replace dummy account with a real one
    const account = 123;
    const invoiceEntryData = {
      invoiceId,
      name,
      account
    };
    dispatch(rest.actions.createInvoiceEntry({}, {
      body: JSON.stringify(invoiceEntryData)
    }));
  };

  handleDeleteSubmit = (event) => {
    event.preventDefault();
    const {dispatch} = this.props;
    dispatch(rest.actions.deleteInvoice({id: `${this.props.match.params.invoiceId}`}));
    // @TODO: Check that deletion is successful before navigating back to project page
    this.props.history.push(`/project/${this.props.match.params.projectId}`);
  };

  handleAddFromJira = (event) => {
    event.preventDefault();
    const {dispatch} = this.props;
    this.props.history.push(`/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}/invoice_entry/jira_issues`);
  };

  handleAddManually = (event) => {
    event.preventDefault();
    const {dispatch} = this.props;
    this.props.history.push({
      pathname: `/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}/submit/invoice_entry`,
      state: {from: this.props.location.pathname}
    });
  };

  // @TODO: Remove form to create invoiceEntry with only a name
  // @TODO: Handle updating the list of invoiceEntries when a new invoiceEntry is submitted
  render () {
    if (this.props.invoice.data.jiraId && this.props.invoice.data.jiraId != this.props.match.params.projectId )  {
      return (
        <ContentWrapper>
          <PageTitle>Invoice</PageTitle>
          <Alert variant="warning">Error: the requested invoice does not match the project specified in the URL</Alert>
          <p>(URL contains projectId '{this.props.match.params.projectId}'
           but invoice with id '{this.props.match.params.invoiceId}'
            belongs to project with id '{this.props.invoice.data.jiraId}')
          </p>
        </ContentWrapper>
      );
    }
    else if (this.props.invoice.data.name) {
      return (
        <ContentWrapper>
          <Row>
            <Col>
              <p className="small text-muted">Invoice for project {this.props.match.params.name}({this.props.match.params.projectId})</p>
              <p>Invoicenumber: <strong className="pr-3">{this.props.match.params.invoiceId}</strong> Invoicedate: <strong>{String(this.props.invoice.data.recorded)}</strong></p>
            </Col>
            <Col className="text-right">
              <Button variant="success" type="submit" id="record-invoice" className="mr-3" onClick={this.saveInvoice}>
                Save invoice
              </Button>
              <Button variant="primary" type="submit" id="record-invoice" className="mr-3" onClick={this.handleRecordSubmit}>
                Record invoice
              </Button>
              <Button variant="danger" type="submit" id="delete" onClick={this.handleDeleteSubmit}>
                Delete invoice
              </Button>
            </Col>
          </Row>
          <hr/>
          <Row>
            <Col md={9}>
              <Form>
                <Form.Row>
                  <Col>
                    <Form.Group controlId="invoiceName" >
                      <Form.Label>Title</Form.Label>
                      <Form.Control type="text" placeholder="Enter name for invoice" defaultValue={this.props.invoice.data.name}/>
                      <Form.Text className="text-muted">
                        The title should help you identify this Invoice later on.
                      </Form.Text>
                    </Form.Group>
                  </Col>
                  <Col>
                    <Form.Group controlId="invoiceDescription" >
                      <Form.Label>Description</Form.Label>
                      <Form.Control type="text" placeholder="Enter a short description" defaultValue="Invoice description TODO: save with invoice data" />
                      <Form.Text className="text-muted">
                        Give a short description to help your customer understand what this Invoice is about.
                      </Form.Text>
                    </Form.Group>
                  </Col>
                </Form.Row>
              </Form>
              <h2>Invoice entries</h2>
              <Row className="mb-3">
                <Col md={6}>
                    <Button variant="outline-success" size="sm" type="submit" className="mr-3" onClick={this.handleAddFromJira}>Add entry from Jira</Button>
                    <Button variant="outline-success" size="sm" type="submit" onClick={this.handleCreateSubmit}>Add manual entry</Button>
                </Col>
                <Col md={6} className="text-right">
                  <ButtonGroup aria-label="Entry actions">
                    <Button variant="primary" size="sm" type="submit" id="editEntry" onClick={this.handleEntryEdit} disabled>
                      Edit entry
                    </Button>
                    <Button variant="danger" size="sm" type="submit" id="deleteEntry" onClick={this.handleEntryDelete} disabled>
                      Delete entry
                    </Button>
                  </ButtonGroup>
                </Col>
              </Row>
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
                  {this.props.invoiceEntries.data.data && this.props.invoiceEntries.data.data.map((item) =>
                    <tr key={item.id}>
                      <td><Form.Check aria-label="" /></td>
                      <td>{item.accountNumber}</td>
                      <td><Link to={`/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}/${item.id}`}>{item.name}</Link></td>
                      <td>{item.description}</td>
                      <td>{item.amount}</td>
                      <td>{item.itemPrice}</td>
                      <td>{item.totalPrice}</td>
                    </tr>
                  )}
                </tbody>
              </Table>
            </Col>
            <Col md={3}>
              <Row>
                <Col><h3>Client</h3></Col>
                <Col><Button size="sm" variant="outline-secondary" className="float-right">Change</Button></Col>
              </Row>
              <ListGroup>
                {[
                  {title: 'Name', content: 'Customer name'},
                  {title: 'Contact', content: 'Customer contact'},
                  {title: 'Account', content: 'XXXX'},
                  {title: 'CVR', content: 'XXXXXXXX'},
                  {title: 'EAN', content: 'XXXXXXXXXX'}
                ].map(({title, content}) => {
                  return (<ListGroup.Item key={title} className="small"><span className="text-muted d-inline-block w-25">{title}</span>{content}</ListGroup.Item>);
                })}
              </ListGroup>
            </Col>
          </Row>
          <ContentFooter>
            Invoice created <strong><Moment format="YYYY-MM-DD HH:mm">{this.props.createdAt}</Moment></strong>
          </ContentFooter>
        </ContentWrapper>
      );
    }
    else {
      return (
        <ContentWrapper>
          <Spinner animation="border" role="status">
            <span className="sr-only">Loading...</span>
          </Spinner>
        </ContentWrapper>
      );
    }
  }
}

Invoice.propTypes = {
  invoice: PropTypes.object,
  createdAt: PropTypes.string,
  invoiceEntries: PropTypes.object,
  dispatch: PropTypes.func.isRequired
};

const mapStateToProps = state => {
  let createdAt = state.invoice.data.created ? state.invoice.data.created.date : '';

  return {
    invoice: state.invoice,
    createdAt: createdAt,
    invoiceEntries: state.invoiceEntries
  };
};

export default connect(
  mapStateToProps
)(Invoice);
