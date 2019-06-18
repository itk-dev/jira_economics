import React from 'react';
import Row from 'react-bootstrap/Row';
import Col from 'react-bootstrap/Col';

export default ({children}) => (
  <Row>
    <Col>
      {children}
    </Col>
  </Row>
)
