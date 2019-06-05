import React  from 'react';
import ProjectList from '../components/ProjectList';
import PageTitle from '../components/PageTitle';
import ContentWrapper from '../components/ContentWrapper';
import Tabs from 'react-bootstrap/Tabs';
import Tab from 'react-bootstrap/Tab';
import Table from 'react-bootstrap/Table';
import Form from 'react-bootstrap/Form';
import ButtonGroup from 'react-bootstrap/ButtonGroup';
import Button from 'react-bootstrap/Button';
import OverlayTrigger from 'react-bootstrap/OverlayTrigger';
import Tooltip from 'react-bootstrap/Tooltip';

export default function HomePage(props) {
  return (
      <div className="row">
        <PageTitle className="col-sm-8">Fakturaer</PageTitle>
        <div className="col-sm-4">
          <Form className="float-right">
            <Form.Group controlId="exampleForm.ControlSelect1">
              <Form.Label>Sorter</Form.Label>
              <Form.Control as="select">
                <option>Nyeste først</option>
                <option>Ældste først</option>
              </Form.Control>
            </Form.Group>
          </Form>
        </div>
        <div className="col-12">
          <Tabs defaultActiveKey="drafts" id="uncontrolled-tab-example">
            <Tab eventKey="drafts" title="Kladder">
              <Table responsive striped hover borderless>
                <thead>
                  <tr>
                    <th>Faktura navn</th>
                    <th>Faktura dato</th>
                    <th>Beløb (DKK)</th>
                    <th className="text-right">Funktion</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><a href="/billing/project/"><strong>Udvikling sommer</strong></a></td>
                    <td>30/09/2018</td>
                    <td><strong>65.146</strong></td>
                    <td className="text-right">
                      <ButtonGroup size="sm" className="float-right" aria-label="Invoice functions">
                        <OverlayTrigger
                          key="edit"
                          placement="top"
                          overlay={
                            <Tooltip id="tooltip-edit">
                              Edit this invoice
                            </Tooltip>
                          }
                        >
                          <Button className="btn-primary">
                            <i className="fas fa-edit mx-2"></i>
                            <span className="sr-only">rediger</span>
                          </Button>
                        </OverlayTrigger>
                        <OverlayTrigger
                          key="delete"
                          placement="top"
                          overlay={
                            <Tooltip id="tooltip-delete">
                              Delete this invoice
                            </Tooltip>
                          }
                        >
                          <Button className="btn-danger">
                            <i className="fas fa-trash-alt mx-2"></i>
                            <span className="sr-only">slet</span>
                          </Button>
                        </OverlayTrigger>

                      </ButtonGroup>
                    </td>
                  </tr>
                </tbody>
              </Table>
            </Tab>
            <Tab eventKey="posted" title="Bogførte">
              <Table responsive striped hover borderless>
                <thead>
                  <tr>
                    <th>Faktura navn</th>
                    <th>Faktura dato</th>
                    <th>Beløb (DKK)</th>
                    <th className="text-right">Funktion</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><a href="/billing/project"><strong>Udvikling sommer</strong></a></td>
                    <td>30/09/2018</td>
                    <td><strong>65.146</strong></td>
                    <td className="text-right">
                      <ButtonGroup className="btn-group-sm float-right" aria-label="Invoice functions">
                        <OverlayTrigger
                          key="download-csv"
                          placement="top"
                          overlay={
                            <Tooltip id="tooltip-download-csv">
                              Download csv file
                            </Tooltip>
                          }
                        >
                          <Button>
                            <i className="fas fa-file-csv mx-2"></i>
                            <span className="sr-only">hent csv</span>
                          </Button>
                        </OverlayTrigger>
                      </ButtonGroup>
                    </td>
                  </tr>
                </tbody>
              </Table>
            </Tab>
          </Tabs>
        </div>
      </div>
  )
}
