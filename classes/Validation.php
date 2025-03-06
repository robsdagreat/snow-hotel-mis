<?php
class Validation {
    /**
     * Validates consumable input data.
     *
     * @param string $item
     * @param string $service_id
     * @param int|null $id
     * @return array List of validation errors (if any)
     */
    public function validateConsumable($item, $service_id, $unit, $unit_price, $id = null) {
        $errors = [];
        if (empty($item)) {
            $errors[] = "Item name is required.";
        }
        if (empty($service_id)) {
            $errors[] = "Service selection is required.";
        }
        if (empty($unit)) {
            $errors[] = "Unit is required.";
        }
        if (empty($unit_price)) {
            $errors[] = "Unit Price is required.";
        }        
        if ($id !== null && !is_numeric($id)) {
            $errors[] = "Invalid consumable ID.";
        }
        return $errors;
    }
}
?>
