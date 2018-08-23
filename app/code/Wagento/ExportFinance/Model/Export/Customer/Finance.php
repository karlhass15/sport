<?php
namespace Wagento\ExportFinance\Model\Export\Customer;

/**
 * Export customer finance entity model
 *
 * @author      Wagento
 * @method      array getData()
 * @codeCoverageIgnore
 */
class Finance extends \Magento\CustomerFinance\Model\Export\Customer\Finance
{
    /**#@+
     * Permanent column names
     *
     * Names that begins with underscore is not an attribute. This name convention is for
     * to avoid interference with same attribute name.
     */
    const COLUMN_CUSTOMERID = '_customer_id';

    /**
     * Permanent entity columns
     *
     * @var string[]
     */
    protected $_permanentAttributes = [self::COLUMN_CUSTOMERID, self::COLUMN_EMAIL, self::COLUMN_WEBSITE, self::COLUMN_FINANCE_WEBSITE];

    /**
     * Export given customer data
     *
     * @param \Magento\Customer\Model\Customer $item
     * @return void
     */
    public function exportItem($item)
    {
        $validAttributeCodes = $this->_getExportAttributeCodes();

        foreach ($this->_websiteIdToCode as $websiteCode) {
            $row = [];
            foreach ($validAttributeCodes as $code) {
                $attributeCode = $websiteCode . '_' . $code;
                $websiteData = $item->getData($attributeCode);
                if (null !== $websiteData) {
                    $row[$code] = $websiteData;
                }
            }

            if (!empty($row)) {
                $row[self::COLUMN_CUSTOMERID] = $item->getEntityId();
                $row[self::COLUMN_EMAIL] = $item->getEmail();
                $row[self::COLUMN_WEBSITE] = $this->_websiteIdToCode[$item->getWebsiteId()];
                $row[self::COLUMN_FINANCE_WEBSITE] = $websiteCode;
                $this->getWriter()->writeRow($row);
            }
        }
    }
}
