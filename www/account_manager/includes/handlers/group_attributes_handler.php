<?php

/**
 * Group Attributes Handler
 * Updates group attributes only; never touches membership, so saving attributes
 * can't remove a group's members (fixes #268).
 */

if (!defined('LDAP_USER_MANAGER')) {
  die('Direct access not permitted');
}

if (isset($_POST["update_attributes"]) and $group_exists == TRUE and count($to_update) > 0) {

  if (isset($this_group[0]['objectclass'])) {
    $existing_objectclasses = $this_group[0]['objectclass'];
    unset($existing_objectclasses['count']);
    if ($existing_objectclasses != $LDAP['group_objectclasses']) { $to_update['objectclass'] = $LDAP['group_objectclasses']; }
  }

  $updated_attr = ldap_update_group_attributes($ldap_connection,$group_cn,$to_update);

  if ($updated_attr) {
    // Audit log group attribute update
    $update_fields = array_keys($to_update);
    $update_details = "Updated fields: " . implode(', ', $update_fields);
    audit_log('group_updated', $group_cn, $update_details, 'success', $USER_ID);
    render_alert_banner("The group attributes have been updated.");
  }
  else {
    // Audit log failed update
    audit_log('group_update_failure', $group_cn, "Failed to update group attributes", 'failure', $USER_ID);
    render_alert_banner("There was a problem updating the group attributes.  See the logs for more information.","danger",15000);
  }

}
