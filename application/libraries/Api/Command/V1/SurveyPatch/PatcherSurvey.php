<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\ObjectPatch\{ObjectPatchException,
    Op\OpStandard,
    OpHandler\OpHandlerException,
    Patcher
};
use Psr\Container\ContainerInterface;

class PatcherSurvey extends Patcher
{
    protected TempIdMapping $tempIdMapping;

    /**
     * Constructor
     *
     * @param ContainerInterface $diContainer
     * @param TempIdMapping $tempIdMapping
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct(
        ContainerInterface $diContainer,
        TempIdMapping $tempIdMapping
    ) {
        $this->tempIdMapping = $tempIdMapping;
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerSurveyUpdate::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerLanguageSettingsUpdate::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionGroup::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionGroupL10n::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionDelete::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionCreate::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionUpdate::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionL10nUpdate::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerAnswer::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionAttributeUpdate::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerQuestionGroupReorder::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerSubquestionDelete::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerAnswerDelete::class
            )
        );
        $this->addOpHandler(
            $diContainer->get(
                OpHandlerSubQuestion::class
            )
        );
    }

    /**
     * Apply patch
     *
     * @param ?mixed $patch
     * @param ?array $context
     * @return array
     * @throws ObjectPatchException
     * @throws OpHandlerException
     */
    public function applyPatch($patch, $context = []): array
    {
        if (is_array($patch) && !empty($patch)) {
            foreach ($patch as $patchOpData) {
                $op = OpStandard::factory(
                    $patchOpData['entity'] ?? '',
                    $patchOpData['op'] ?? '',
                    $patchOpData['id'] ?? '',
                    $patchOpData['props'] ?? [],
                    $context ?? []
                );
                $this->tempIdMapping->incrementOperationsApplied();
                $handleResponse = $this->handleOp($op);
                foreach ($handleResponse as $groupName => $mappingItem) {
                    $this->addTempIdMapItem($mappingItem, $groupName);
                }
            }
        }
        return $this->tempIdMapping->getMappingResponseObject();
    }

    /**
     * Recursive function to extract TempIdMapItems from the $mappingItem
     * @param TempIdMapItem|array $mappingItem
     * @param string $groupName
     * @return void
     * @throws \LimeSurvey\ObjectPatch\OpHandler\OpHandlerException
     */
    private function addTempIdMapItem($mappingItem, string $groupName)
    {
        if ($mappingItem instanceof TempIdMapItem) {
            $this->tempIdMapping->addTempIdMapItem(
                $mappingItem,
                $groupName
            );
        } else {
            foreach ($mappingItem as $item) {
                $this->addTempIdMapItem($item, $groupName);
            }
        }
    }
}
