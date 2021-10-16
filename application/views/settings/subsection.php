<?php
display_messages();

if ($type == 'defaults') {
    echo form_open_multipart($form_action, array('class' => 'settings'));
} else {
    echo form_open_multipart($form_action, array('class' => 'settings'));
}
echo form_hidden(['action' => 'process']);
if (count($subsection_data) > 0) {
    $prev_section = NULL;
    $i = 0;
    $array = array();
    foreach ($subsection_data as $setting) {
        $array[$setting->key] = set_value($setting->key, $this->settings_library->get($setting->key), FALSE);
        if (!in_array($setting->key, $keyArray)) {
            // check for required features if not in default settings
            if (!empty($setting->required_features) && $type !== 'defaults') {
                $required_features = (array)explode(" ", $setting->required_features);
                $required_features = array_filter($required_features);
                if (count($required_features) > 0 && !$this->auth->has_features($required_features)) {
                    continue;
                }
            }

            if ($setting->section != $prev_section) {
                $prev_section = $setting->section;
                if ($i > 0) {
                    echo form_fieldset_close();
                    ?>
                    <hr class="hr-normal"/><?php
                }
                echo form_fieldset('', ['class' => 'card card-custom']);
                if ($flag == 0) {
                    ?>
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="card-label">Details</h3>
                        </div>
                    </div><?php
                } ?>

                <div class="card-body">
                <div class='
                <?php if ($setting->section == 'styling') {
                        echo 'col-sm-8 pl-0';
                    } else {
                        echo 'multi-columns';
                    } ?>
                '><?php
            }

            //Group these subject and wysiwyg inputs together so they arent split between columns
            if (in_array($setting->key, array("email_confirm_subscription_subject", "email_cancel_subscription_subject", "email_update_subscription_subject"))) {
                echo "<div class='form-group'>";
            }
            if ($setting->key !== 'salaried_sessions') {
                ?>
                <div class='form-group'><?php
                echo form_label($setting->title, $setting->key);
                $data = array(
                    'name' => $setting->key,
                    'id' => $setting->key,
                    'class' => 'form-control',
                    'value' => set_value($setting->key, $this->settings_library->get($setting->key), FALSE)
                );
                if ($type == 'defaults') {
                    $data['value'] = set_value($setting->key, $this->settings_library->get($setting->key, 'default'), FALSE);
                }
                if (isset($setting->readonly)) {
                    if ($setting->readonly == 1) {
                        $data['readonly'] = 1;
                    }
                }

                if ($setting->key == 'email_from_override' && empty($data['value'])) {
                    $data['value'] = $this->settings_library->get('email_from_default', 'default');
                }


                if ($setting->key == 'department_email_name' && empty($setting->value)) {
                    $data['value'] = '';
                }

                if ($setting->subsection == 'departments_emailsms' && !empty($setting->value)) {
                    $data['value'] = $setting->value;
                }

                switch ($setting->type) {
                    case 'text':
                    default:
                        if (substr($setting->type, 0, 4) == 'date') {
                            $data['class'] .= ' datepicker';
                            $data['value'] = mysql_to_uk_date($data['value']);
                            if ($setting->type == 'date-monday') {
                                $data['data-onlyday'] = 'monday';
                            }
                        }
                        echo form_input($data);
                        break;
                    case 'email':
                    case 'email-multiple':
                        if ($setting->type == 'email-multiple') {
                            $data['multiple'] = 'multiple';
                        }
                        echo form_email($data);
                        break;
                    case 'url':
                        echo form_url($data);
                        break;
                    case 'tel':
                        echo form_telephone($data);
                        break;
                    case 'number':
                        echo form_number($data);
                        break;
                    case 'textarea':
                    case 'html':
                    case 'css':
                        if (in_array($setting->type, array('css', 'html'))) {
                            $data['data-editor'] = $setting->type;
                        }
                        echo form_textarea($data);
                        break;
                    case 'checkbox':
                    case 'function':
                        if ($data['value'] == 1) {
                            $data['checked'] = TRUE;
                        }
                        $data['value'] = 1;
                        $data['class'] = 'auto';
                        if ($setting->type == 'function') {
                            $data['checked'] = FALSE;
                            $data['class'] .= ' confirm';
                        }
                        if (!empty($setting->toggle_fields)) {
                            $data['data-toggle_fields'] = $setting->toggle_fields;
                        }
                        ?>
                        <div class="checkbox-single">
                        <label class="checkbox">
                            <?php echo form_checkbox($data); ?>
                            Yes
                            <span></span>
                        </label>
                        </div><?php
                        break;
                    case 'wysiwyg':
                        $data['class'] .= ' wysiwyg';
                        echo form_textarea($data);
                        break;
                    case 'image':
                        $image_data = @unserialize($data['value']);
                        if ($image_data !== FALSE) {
                            $args = array(
                                'alt' => 'Image',
                                'src' => 'attachment/setting/' . $setting->key,
                                'class' => 'responsive-img'
                            );
                            if ($type == 'defaults') {
                                $args['src'] .= '/default';
                            }
                            echo '<p>' . img($args) . '</p>';
                            if ($setting->key == 'online_booking_header_image') {
                                $checkbox_data = array(
                                    'name' => 'remove_' . $setting->key,
                                    'class' => 'auto',
                                    'value' => 1
                                );
                                ?>
                                <div class="checkbox-single">
                                <label class="checkbox">
                                    <?php echo form_checkbox($checkbox_data); ?>
                                    Remove <?php echo $setting->title; ?>
                                    <span></span>
                                </label>
                                </div><?php }
                            echo form_label('Replace ' . $setting->title, $setting->key);
                        }
                        $data['class'] = ' custom-file-input';
                        ?>
                        <div class="custom-file">
                        <?php echo form_upload($data); ?>
                        <label class="custom-file-label" for="<?php echo $setting->key; ?>">Choose file</label>
                        </div><?php
                        break;
                    case 'staff':
                        $options = array(
                            '' => 'Select'
                        );
                        if ($staff_list->num_rows() > 0) {
                            foreach ($staff_list->result() as $row) {
                                $options[$row->staffID] = $row->first . ' ' . $row->surname;
                            }
                        }
                        echo form_dropdown($setting->key, $options, $data['value'], 'id="' . $setting->key . '" class="form-control select2"');
                        break;

                    case 'brand_tags':
                        $options = array();
                        $selected = array();
                        $already_selected_department = null;
                        if ($selected_department->num_rows() > 0) {
                            foreach ($selected_department->result() as $item) break;
                            $already_selected_department = $item->brand_name;
                        }
                        if ($brand_list->num_rows() > 0) {
                            foreach ($brand_list->result() as $row) {
                                if (!empty($already_selected_department) &&
                                    strpos($already_selected_department, $row->name) !== false &&
                                    strpos($setting->value, $row->name) === false
                                ) {
                                    continue;
                                }
                                if (strpos($setting->value, $row->name) !== false) {
                                    $selected[] = $row->brandID;
                                }
                                $options[$row->brandID] = $row->name;
                            }
                        }
                        echo form_dropdown($setting->key, $options, $data['value'], 'id="' . $setting->key . '" class="form-control select2"');
                        break;
                    case 'select':
                        $options = array(
                            '' => 'Select'
                        );
                        $options_list = explode("\n", $setting->options);
                        if (count($options_list) > 0) {
                            foreach ($options_list as $option) {
                                $option_parts = explode(" : ", $option);
                                if (count($option_parts) == 2) {
                                    $options[$option_parts[0]] = $option_parts[1];
                                }
                            }
                        }
                        echo form_dropdown($setting->key, $options, $data['value'], 'id="' . $setting->key . '" class="form-control select2"');
                        break;
                    case 'permission-levels':
                        $options = array(
                            'directors' => $this->settings_library->get_permission_level_label('directors'),
                            'management' => $this->settings_library->get_permission_level_label('management'),
                            'office' => $this->settings_library->get_permission_level_label('office'),
                            'headcoach' => $this->settings_library->get_permission_level_label('headcoach'),
                            'fulltimecoach' => $this->settings_library->get_permission_level_label('fulltimecoach'),
                            'coaching' => $this->settings_library->get_permission_level_label('coaching')
                        );
                        echo form_dropdown($setting->key . '[]', $options, $data['value'], 'id="' . $setting->key . '"  multiple="multiple" class="form-control select2"');
                        break;
                }
                if (!empty($setting->instruction)) {
                    ?><small class="text-muted form-text"><?php echo str_replace(array('{site_url}', '{account_id}'), array(site_url(), $this->auth->user->accountID), $setting->instruction); ?></small><?php
                }
                ?></div><?php
            }
            $i++;
            if (in_array($setting->key, array("email_confirm_subscription", "email_cancel_subscription", "email_update_subscription"))) {
                echo "</div>";
            }
        }
    }
    ?></div><?php
    echo form_fieldset_close();
    foreach($subsection_data as $payrollSetting) {
        if($payrollSetting->section == 'general' && $payrollSetting->key == 'salaried_sessions') {
            echo form_fieldset('', ['class' => 'card card-custom']);

            ?>
            <div class='card-header'>
                <div class="card-title">
                    <h3 class="card-label">Advanced Payroll Settings</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <?php
                        echo form_label($payrollSetting->title, $payrollSetting->key);
                        $payrollData = array(
                            'name' => $payrollSetting->key,
                            'id' => $payrollSetting->key,
                            'class' => 'form-control',
                            'value' => set_value($payrollSetting->key, $this->settings_library->get($payrollSetting->key), FALSE)
                        );
                        if ($payrollData['value'] == 1) {
                            $payrollData['checked'] = TRUE;
                        }
                        $payrollData['value'] = 1;
                        $payrollData['class'] = 'auto';
                        if ($payrollSetting->type == 'function') {
                            $payrollData['checked'] = FALSE;
                            $payrollData['class'] .= ' confirm';
                        }
                        if (!empty($payrollSetting->toggle_fields)) {
                            $payrollData['data-toggle_fields'] = $payrollSetting->toggle_fields;
                        }
                    ?>
                    <div class="checkbox-single">
                        <label class="checkbox">
                            <?php echo form_checkbox($payrollData); ?>
                            Yes
                            <span></span>
                        </label>
                    </div>
                </div>
                <div class="multi-columns">
                    <div class="form-group">
                        <?php
                        $salariedSessionData['name'] = 'salaried_sessions_options[0]';
                        $salariedSessionData['value'] = 'Salaried Session';
                        $salariedSessionData['class'] = 'form-control';
                        echo form_input($salariedSessionData);
                        ?>
                        <small class="text-muted">
                            You can customise the name of Salaried sessions here. This will appear in Timesheets, the Payroll Report and in Sessions.
                        </small>
                    </div>
                    <div class="form-group">
                        <?php
                        $nonSalariedSessionData['name'] = 'salaried_sessions_options[1]';
                        $nonSalariedSessionData['value'] = 'Non-Salaried Session';
                        $nonSalariedSessionData['class'] = 'form-control';
                        echo form_input($nonSalariedSessionData);
                        ?>
                        <small class="text-muted">
                            You can customise the name of Non-Salaried sessions here. This will appear in Timesheets, the Payroll Report and in Sessions.
                        </small>
                    </div>
                </div>
            </div>
            <?php
            echo form_fieldset_close();
        }
    }
} else {
    ?><p>No settings</p><?php
}
?>
<?php if ($flag == 0) { ?>
    <div class="card card-custom mileage_section">
        <div class="card-header" style="cursor: pointer;">
            <div class="card-title">
                <h3 class="card-label">Mileage</h3>
            </div>
            <div class="card-toolbar">
                <a class="btn font-weight-bold btn-sm btn-success" href="<?php echo site_url('settings/listing/general/timesheets_general/new') ?>"><i class="far fa-plus"></i> Create New</a>
            </div>
        </div>
        <div class="card-body">
            <div class='table-responsive'>
                <div class='scrollable-area'>
                    <h5> Mileage Rates </h5>
                    <table class='table table-striped table-bordered'>
                        <thead>
                        <tr>
                            <th class="">Name</th>
                            <th class="">Rate</th>
                            <th class=""></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if (count($mileage_list->result()) > 0) {
                            $count = 1;
                            $carCounter = 0;
                            foreach ($mileage_list->result() as $row) {
                                if (strtolower($row->name) == 'car')
                                    $carCounter++;
                                echo "
									<tr>
										<td> " . $row->name . " </td>
										<td> " . $row->rate . strtolower(substr(currency_small_symbol(), 0, 1)) . " per mile </td>
										<td>
											<a class='btn btn-warning btn-sm' href='" . site_url('settings/listing/general/timesheets_general/' . $row->mileageID) . "' title='Edit'>
												<i class='far fa-pencil fa-fw'></i>
											</a>&nbsp;";
                                if ((strtolower($row->name) == 'car' && $carCounter != 1) || strtolower($row->name) != 'car') {
                                    echo "<a class='btn btn-danger confirm-delete btn-sm' href='" . site_url('settings/listing_remove/general/timesheets_general/' . $row->mileageID) . "' title='Remove'>
												<i class='far fa-trash fa-fw'></i>
												</a>";
                                }
                                echo "
										</td>
									</tr>";
                            }
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row pt-5 pb-10">
                <div class="col-md-8">
                    <div class="form-group pt-3">
                        <?php
                        echo form_label("Default Mode of Transport <em>*</em>");
                        $options = array(
                            '' => 'Select'
                        );
                        $carid = '';
                        foreach ($mileage_list->result() as $row) {
                            $options[$row->mileageID] = $row->name;
                            if ($row->name == 'Car')
                                $carid = $row->mileageID;
                        }
                        echo form_dropdown('mileage_default_mode_of_transport', $options, ((isset($array["mileage_default_mode_of_transport"]) && $array["mileage_default_start_location"] != null) ? $array["mileage_default_mode_of_transport"] : $carid), 'id="mileage_default_mode_of_transport" class="form-control select2"');
                        ?>
                    </div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-8">
                    <div class="form-group pt-3">
                        <label for="name">Excluded Mileage with Fuel Card</label>
                        <div class="input-group">
                            <input type="text" name="excluded_mileage" value="<?php echo isset($array["excluded_mileage"]) ? $array["excluded_mileage"] : ''; ?>" id="excluded_mileage" class="form-control">
                            <div class="input-group-append"><span class="input-group-text">Miles</span></div>
                        </div>
                        <small>The mileage entered here will be excluded every day from each member of staff that uses a Fuel Card</small>
                    </div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-8">
                    <div class="form-group pt-3">
                        <label for="name">Excluded Mileage without Fuel Card</label>
                        <div class="input-group">
                            <input type="text" name="excluded_mileage_without_fuel_card" value="<?php echo isset($array["excluded_mileage_without_fuel_card"]) ? $array["excluded_mileage_without_fuel_card"] : ''; ?>"
                                   id="excluded_mileage_without_fuel_card" class="form-control">
                            <div class="input-group-append"><span class="input-group-text">Miles</span></div>
                        </div>
                        <small>The mileage entered here will be excluded every day from each member of staff that does not use a fuel card</small>
                    </div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-8">
                    <div class="form-group pt-3">
                        <?php
                        echo form_label("Default Start Location <em>*</em>");
                        $options = array(
                            '' => 'Select'
                        );
                        $options["staff_main_address"] = "Staff Main Address";
                        $options["work_address"] = "Work Address";
                        $ids = "staff_main_address";
                        echo form_dropdown('mileage_default_start_location', $options, ((isset($array["mileage_default_start_location"]) && $array["mileage_default_start_location"] != null) ? $array["mileage_default_start_location"] : $ids), 'id="mileage_default_start_location" class="form-control select2"');
                        ?>
                    </div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-12">
                    <div class="form-group pt-3">
                        <div class="checkbox-single">
                            <label class="checkbox">
                                <?php
                                $data = array();
                                $display = 'none';
                                if (isset($array["mileage_activate_fuel_cards"]) && $array["mileage_activate_fuel_cards"] == 1) {
                                    $data['checked'] = TRUE;
                                    $display = 'block';
                                }
                                $data['value'] = 1;
                                $data['class'] = 'auto';
                                $data['id'] = 'mileage_activate_fuel_cards';
                                $data['name'] = 'mileage_activate_fuel_cards';
                                echo form_checkbox($data); ?>
                                Activate Fuel Cards
                                <span></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 automatically_approve" style="display:<?php echo $display ?>">
                    <div class="form-group">
                        <div class="checkbox-single">
                            <label class="checkbox">
                                <?php
                                $data = array();
                                if (isset($array["automatically_approve_fuel_card"]) && $array["automatically_approve_fuel_card"] == 1) {
                                    $data['checked'] = TRUE;
                                }
                                $data['value'] = 1;
                                $data['class'] = 'auto';
                                $data['name'] = 'automatically_approve_fuel_card';
                                echo form_checkbox($data); ?>
                                Automatically Approve Fuel Card Mileage
                                <span></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <h4> Default Work Address </h4>

            <div class="multi-columns pt-10">
                <div class="form-group">
                    <?php
                    echo form_label("Address 1", "mileage_default_address1");
                    $data = array();
                    $data['class'] = 'form-control';
                    $data['value'] = isset($array["mileage_default_address1"]) ? $array["mileage_default_address1"] : '';
                    $data['name'] = 'mileage_default_address1';
                    echo form_input($data);
                    ?>
                </div>
                <div class="form-group">
                    <?php
                    echo form_label("Address 2", "mileage_default_address2");
                    $data = array();
                    $data['class'] = 'form-control';
                    $data['value'] = isset($array["mileage_default_address2"]) ? $array["mileage_default_address2"] : '';
                    $data['name'] = 'mileage_default_address2';
                    echo form_input($data);
                    ?>
                </div>
                <div class="form-group">
                    <?php
                    echo form_label("Post Code", "mileage_default_postcode");
                    $data = array();
                    $data['class'] = 'form-control';
                    $data['value'] = isset($array["mileage_default_postcode"]) ? $array["mileage_default_postcode"] : '';
                    $data['name'] = 'mileage_default_postcode';
                    echo form_input($data);
                    ?>
                </div>
                <div class="form-group">
                    <?php
                    echo form_label("Town", "mileage_default_town");
                    $data = array();
                    $data['class'] = 'form-control';
                    $data['name'] = 'mileage_default_town';
                    $data['value'] = isset($array["mileage_default_town"]) ? $array["mileage_default_town"] : '';
                    echo form_input($data);
                    ?>
                </div>
                <div class="form-group">
                    <?php
                    echo form_label(localise('county'), "mileage_default_county");
                    $data = array();
                    $data['class'] = 'form-control';
                    $data['value'] = isset($array["mileage_default_county"]) ? $array["mileage_default_county"] : '';
                    $data['name'] = 'mileage_default_county';
                    echo form_input($data);
                    ?>
                </div>
            </div>
        </div>
    </div>

<?php } ?>
    <div class='form-actions d-flex justify-content-between'>
        <button class='btn btn-primary btn-submit' type="submit">
            <i class='far fa-save'></i> Save
        </button>
    </div>
<?php echo form_close();
if (!is_array($customers_org_type)) {
    $this->load->view("settings/customers_org_types", $customers_org_type);
}

