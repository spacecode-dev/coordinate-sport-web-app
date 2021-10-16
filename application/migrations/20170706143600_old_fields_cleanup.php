<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Old_fields_cleanup extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // remove fields
        $this->dbforge->drop_column('bookings', 'provisional_old');
        $this->dbforge->drop_column('bookings', 'price_pe_old');
        $this->dbforge->drop_column('bookings', 'price_pe_contract_old');
        $this->dbforge->drop_column('bookings', 'price_ppa_old');
        $this->dbforge->drop_column('bookings', 'price_ppa_contract_old');
        $this->dbforge->drop_column('bookings', 'price_ssp_old');
        $this->dbforge->drop_column('bookings', 'price_ssp_contract_old');
        $this->dbforge->drop_column('bookings', 'price_extracurricular_old');
        $this->dbforge->drop_column('bookings', 'price_extracurricular_contract_old');
        $this->dbforge->drop_column('bookings', 'price_oneoff_old');
        $this->dbforge->drop_column('bookings', 'price_oneoff_contract_old');
        $this->dbforge->drop_column('bookings', 'price_other_old');
        $this->dbforge->drop_column('bookings', 'price_other_contract_old');
        $this->dbforge->drop_column('bookings', 'price_community_old');
        $this->dbforge->drop_column('bookings', 'price_community_contract_old');
        $this->dbforge->drop_column('bookings', 'price_sportunlimited_old');
        $this->dbforge->drop_column('bookings', 'price_sportunlimited_contract_old');
        $this->dbforge->drop_column('bookings', 'price_leadersaward_old');
        $this->dbforge->drop_column('bookings', 'price_leadersaward_contract_old');
        $this->dbforge->drop_column('bookings', 'price_holcamp_old');
        $this->dbforge->drop_column('bookings', 'price_holcamp_contract_old');
        $this->dbforge->drop_column('bookings', 'price_academy_old');
        $this->dbforge->drop_column('bookings', 'price_academy_contract_old');
        $this->dbforge->drop_column('bookings', 'price_birthday_old');
        $this->dbforge->drop_column('bookings', 'price_birthday_contract_old');
        $this->dbforge->drop_column('bookings', 'price_staff_old');
        $this->dbforge->drop_column('bookings', 'price_staff_contract_old');
        $this->dbforge->drop_column('bookings', 'price_earlydropoff_old');
        $this->dbforge->drop_column('bookings', 'price_earlydropoff_contract_old');
        $this->dbforge->drop_column('bookings', 'price_latepickup_old');
        $this->dbforge->drop_column('bookings', 'price_latepickup_contract_old');
        $this->dbforge->drop_column('bookings', 'price_project_old');
        $this->dbforge->drop_column('bookings', 'price_project_contract_old');
        $this->dbforge->drop_column('bookings', 'price_bikeability_old');
        $this->dbforge->drop_column('bookings', 'price_bikeability_contract_old');
        $this->dbforge->drop_column('bookings', 'price_training_old');
        $this->dbforge->drop_column('bookings', 'price_training_contract_old');
        $this->dbforge->drop_column('bookings', 'price_enrichment_old');
        $this->dbforge->drop_column('bookings', 'price_enrichment_contract_old');

        $this->dbforge->drop_column('bookings_lessons', 'activity_old');
        $this->dbforge->drop_column('bookings_lessons', 'type_old');

        $this->dbforge->drop_column('orgs', 'price_pe_old');
        $this->dbforge->drop_column('orgs', 'price_pe_contract_old');
        $this->dbforge->drop_column('orgs', 'price_ppa_old');
        $this->dbforge->drop_column('orgs', 'price_ppa_contract_old');
        $this->dbforge->drop_column('orgs', 'price_ssp_old');
        $this->dbforge->drop_column('orgs', 'price_ssp_contract_old');
        $this->dbforge->drop_column('orgs', 'price_extracurricular_old');
        $this->dbforge->drop_column('orgs', 'price_extracurricular_contract_old');
        $this->dbforge->drop_column('orgs', 'price_oneoff_old');
        $this->dbforge->drop_column('orgs', 'price_oneoff_contract_old');
        $this->dbforge->drop_column('orgs', 'price_other_old');
        $this->dbforge->drop_column('orgs', 'price_other_contract_old');
        $this->dbforge->drop_column('orgs', 'price_community_old');
        $this->dbforge->drop_column('orgs', 'price_community_contract_old');
        $this->dbforge->drop_column('orgs', 'price_sportunlimited_old');
        $this->dbforge->drop_column('orgs', 'price_sportunlimited_contract_old');
        $this->dbforge->drop_column('orgs', 'price_leadersaward_old');
        $this->dbforge->drop_column('orgs', 'price_leadersaward_contract_old');
        $this->dbforge->drop_column('orgs', 'price_holcamp_old');
        $this->dbforge->drop_column('orgs', 'price_holcamp_contract_old');
        $this->dbforge->drop_column('orgs', 'price_academy_old');
        $this->dbforge->drop_column('orgs', 'price_academy_contract_old');
        $this->dbforge->drop_column('orgs', 'price_birthday_old');
        $this->dbforge->drop_column('orgs', 'price_birthday_contract_old');
        $this->dbforge->drop_column('orgs', 'price_staff_old');
        $this->dbforge->drop_column('orgs', 'price_staff_contract_old');
        $this->dbforge->drop_column('orgs', 'price_earlydropoff_old');
        $this->dbforge->drop_column('orgs', 'price_earlydropoff_contract_old');
        $this->dbforge->drop_column('orgs', 'price_latepickup_old');
        $this->dbforge->drop_column('orgs', 'price_latepickup_contract_old');
        $this->dbforge->drop_column('orgs', 'price_project_old');
        $this->dbforge->drop_column('orgs', 'price_project_contract_old');
        $this->dbforge->drop_column('orgs', 'price_bikeability_old');
        $this->dbforge->drop_column('orgs', 'price_bikeability_contract_old');
        $this->dbforge->drop_column('orgs', 'price_training_old');
        $this->dbforge->drop_column('orgs', 'price_training_contract_old');
        $this->dbforge->drop_column('orgs', 'price_enrichment_old');
        $this->dbforge->drop_column('orgs', 'price_enrichment_contract_old');

        $this->dbforge->drop_column('staff', 'activity_games_old');
        $this->dbforge->drop_column('staff', 'activity_sport_old');
        $this->dbforge->drop_column('staff', 'activity_dance_old');
        $this->dbforge->drop_column('staff', 'activity_gymnastics_old');
        $this->dbforge->drop_column('staff', 'activity_cheer_old');
        $this->dbforge->drop_column('staff', 'activity_oaa_old');
        $this->dbforge->drop_column('staff', 'activity_athletics_old');
        $this->dbforge->drop_column('staff', 'activity_bikeability_old');
        $this->dbforge->drop_column('staff', 'activity_holidaycamps_old');
        $this->dbforge->drop_column('staff', 'qual_level1_old');
        $this->dbforge->drop_column('staff', 'qual_level2_old');

        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices') . '` DROP FOREIGN KEY `fk_staff_invoices_timesheetID`');
        $this->dbforge->drop_column('staff_invoices', 'timesheetID_old');

        $this->dbforge->drop_column('vouchers', 'applies_to_old');
    }

    public function down() {
        // do nothing
    }
}