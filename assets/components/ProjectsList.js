// @flow
/* sample-data.js */
import React from 'react';
import styled from 'styled-components';
import projects from '../content/sample-data/projects.json';
import BillingFilledIcon from '@atlaskit/icon/glyph/billing-filled';
import MediaServicesPdfIcon from '@atlaskit/icon/glyph/media-services/pdf';
import Button, { ButtonGroup } from '@atlaskit/button';
import Icon from '@atlaskit/icon';

function createKey(input) {
  return input ? input.replace(/^(the|a|an)/, '').replace(/\s/g, '') : input;
}

const NameWrapper = styled.span`
  display: flex;
  align-items: center;
`;

export const createHead = (withWidth: boolean) => {
  return {
    cells: [
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

export const rows = projects.map((project, index) => ({
  key: `row-${index}-${project.nm}`,
  cells: [
    {
      key: createKey(project.nm),
      content: (
        <NameWrapper>
          <a href={"https://itkdev.atlassian.net/browse/" + project.sn}>{project.nm}</a>
        </NameWrapper>
      ),
    },
    {
      key: createKey(project.sn),
      content: project.sn,
    },
    {
      content: (
        <ButtonGroup>
            <Button href="/billing" iconBefore={<Icon glyph={BillingFilledIcon} label="Billing" size="medium" />}>Billing</Button>
            <Button href="sprint_report/project/" iconBefore={<Icon glyph={MediaServicesPdfIcon} label="Sprint report" size="medium" />}>Sprint report</Button>
        </ButtonGroup>
      ),
    },
  ],
}));
