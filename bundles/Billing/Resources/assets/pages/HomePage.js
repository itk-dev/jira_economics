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
import DropdownButton from 'react-bootstrap/DropdownButton';
import Dropdown from 'react-bootstrap/Dropdown';

export default function HomePage(props) {
  return (
    <ContentWrapper>
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
      </div>
        <Tabs defaultActiveKey="drafts" id="uncontrolled-tab-example">
          <Tab eventKey="drafts" title="Kladder">
            <Table striped hover borderless>
              <thead>
                <tr>
                  <th>Faktura navn</th>
                  <th>Faktura dato</th>
                  <th>Beløb (DKK)</th>
                  <th className="float-right">Funktion</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>Udvikling sommer</strong></td>
                  <td>30/09/2018</td>
                  <td><strong>65.146</strong></td>
                  <td className="float-right">
                    <ButtonGroup size="sm" className="float-right" aria-label="Invoice functions">
                      <Button className="btn-primary">rediger</Button>
                      <Button className="btn-danger">slet</Button>
                    </ButtonGroup>
                  </td>
                </tr>
                <tr>
                  <td><strong>Udvikling sommer</strong></td>
                  <td>30/09/2018</td>
                  <td><strong>65.146</strong></td>
                  <td className="float-right">
                    <ButtonGroup size="sm" className="float-right" aria-label="Invoice functions">
                      <Button className="btn-primary">rediger</Button>
                      <Button className="btn-danger">slet</Button>
                    </ButtonGroup>
                  </td>
                </tr>
              </tbody>
            </Table>
          </Tab>
          <Tab eventKey="posted" title="Bogførte">
            <Table striped hover borderless>
              <thead>
                <tr>
                  <th>Faktura navn</th>
                  <th>Faktura dato</th>
                  <th>Beløb (DKK)</th>
                  <th className="float-right">Funktion</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>Udvikling sommer</strong></td>
                  <td>30/09/2018</td>
                  <td><strong>65.146</strong></td>
                  <td className="float-right">
                    <ButtonGroup className="btn-group-sm float-right" aria-label="Invoice functions">
                      <Button className="btn-outline-primary">hent csv</Button>
                    </ButtonGroup>
                  </td>
                </tr>
                <tr>
                  <td><strong>Udvikling sommer</strong></td>
                  <td>30/09/2018</td>
                  <td><strong>65.146</strong></td>
                  <td className="float-right">
                    <ButtonGroup className="btn-group-sm float-right" aria-label="Invoice functions">
                      <Button className="btn-outline-primary">hent csv</Button>
                    </ButtonGroup>
                  </td>
                </tr>
              </tbody>
            </Table>
          </Tab>
        </Tabs>
    </ContentWrapper>
  )
}
