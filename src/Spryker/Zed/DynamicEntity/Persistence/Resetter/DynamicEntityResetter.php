<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DynamicEntity\Persistence\Resetter;

use Generated\Shared\Transfer\DynamicEntityConfigurationTransfer;
use Generated\Shared\Transfer\DynamicEntityTransfer;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;

class DynamicEntityResetter implements DynamicEntityResetterInterface
{
    public function resetNotProvidedFields(
        ActiveRecordInterface $activeRecord,
        DynamicEntityTransfer $dynamicEntityTransfer,
        DynamicEntityConfigurationTransfer $dynamicEntityConfigurationTransfer
    ): ActiveRecordInterface {
        $filledInFields = $dynamicEntityTransfer->getFields();
        $identifierFieldName = $dynamicEntityConfigurationTransfer->getDynamicEntityDefinitionOrFail()->getIdentifierOrFail();
        foreach ($dynamicEntityConfigurationTransfer->getDynamicEntityDefinitionOrFail()->getFieldDefinitions() as $fieldDefinitionTransfer) {
            $fieldName = $fieldDefinitionTransfer->getFieldNameOrFail();
            if (isset($filledInFields[$fieldName]) || $fieldName === $identifierFieldName) {
                continue;
            }

            $activeRecord->setByName($fieldName, null);
        }

        return $activeRecord;
    }
}
