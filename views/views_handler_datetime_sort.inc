<?php

/**
 * sort handler to properly pass the field type for date fields.
 *
 */
class tincan_lrs_handler_sort_date extends views_handler_sort_date {

  /**
   * Called to add the sort to a query.
   * Overridden to pass the field type
   */
  function query() {
    $this->ensure_my_table();
    switch ($this->options['granularity']) {
      case 'second':
      default:
        $this->query->add_orderby($this->table_alias, $this->real_field, $this->options['order']);
        return;

      case 'minute':
        $formula = views_date_sql_format('YmdHi', "$this->table_alias.$this->real_field", 'datetime');
        break;

      case 'hour':
        $formula = views_date_sql_format('YmdH', "$this->table_alias.$this->real_field", 'datetime');
        break;

      case 'day':
        $formula = views_date_sql_format('Ymd', "$this->table_alias.$this->real_field", 'datetime');
        break;

      case 'month':
        $formula = views_date_sql_format('Ym', "$this->table_alias.$this->real_field", 'datetime');
        break;

      case 'year':
        $formula = views_date_sql_format('Y', "$this->table_alias.$this->real_field", 'datetime');
        break;
    }
    // Add the field.
    $this->query->add_orderby(NULL, $formula, $this->options['order'], $this->table_alias . '_' . $this->field . '_' . $this->options['granularity']);
  }
}

