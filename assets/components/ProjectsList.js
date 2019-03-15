import React from 'react';
import styled from 'styled-components';
import BillingFilledIcon from '@atlaskit/icon/glyph/billing-filled';
import MediaServicesPdfIcon from '@atlaskit/icon/glyph/media-services/pdf';
import Button, { ButtonGroup } from '@atlaskit/button';
import Icon from '@atlaskit/icon';
//import projects from '../content/sample-data/projects.json';

const NameWrapper = styled.span`
  display: flex;
  align-items: center;
`;

const imageStyle = {
  maxWidth: '20px'
};

export const createHead = (withWidth) => {
  return {
    cells: [
      {
        key: 'avatar',
        content: '',
        isSortable: false,
        width: withWidth ? 2 : undefined,
      },
      {
        key: 'name',
        content: 'Name',
        isSortable: true,
        width: withWidth ? 25 : undefined,
      },
      {
        key: 'shortname',
        content: 'Shortname',
        shouldTruncate: true,
        isSortable: true,
        width: withWidth ? 15 : undefined,
      },
      {
        key: 'tools',
        content: 'Tools',
        width: withWidth ? 25 : undefined,
      },
    ],
  };
};

export const head = createHead(true);

export const createRows = (projects) => {
  if (projects === undefined) {
    return [];
  }

  return projects.map((project, index) => ({
    key: `row-${project.id}`,
    values: {
      name: project.name,
      key: project.key
    },
    cells: [
      {
        key: `avatar-${project.id}`,
        content: (
          <img src={project.avatarUrls['16x16']} style={imageStyle}/>
        )
      },
      {
        key: `name-${project.id}`,
        content: (
          <NameWrapper>
            <a
              href={project.self}>{project.name}</a>
          </NameWrapper>
        ),
      },
      {
        key: `shortname-${project.id}`,
        content: project.key,
      },
      {
        key: `tools-${project.id}`,
        content: (
          <ButtonGroup>
            <Button href="/billing" iconBefore={<Icon glyph={BillingFilledIcon}
              label="Billing" size="medium"/>}>Billing</Button>
            <Button href="sprint_report/project/" iconBefore={<Icon glyph={MediaServicesPdfIcon}
              label="Sprint report" size="medium"/>}>Sprint report</Button>
          </ButtonGroup>
        ),
      },
    ],
  }));
};

