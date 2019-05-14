import React from 'react';
import styled from 'styled-components';

const gridSize = 2;

const Padding = styled.div`
  margin: ${gridSize * 4}px ${gridSize * 8}px;
  padding-bottom: ${gridSize * 3}px;
`;

export default ({children}) => (
    <Padding>{children}</Padding>
)
