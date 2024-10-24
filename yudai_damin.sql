--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'SQL_ASCII';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: pgcrypto; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;


--
-- Name: EXTENSION pgcrypto; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION pgcrypto IS 'cryptographic functions';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = true;

--
-- Name: chatbox; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE chatbox (
    chat_id integer,
    kind integer,
    send_id integer,
    receive_id integer,
    s_flag character(1),
    r_flag character(1),
    mess text,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.chatbox OWNER TO yournet;

--
-- Name: chatbox_chat_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE chatbox_chat_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.chatbox_chat_id_seq OWNER TO yournet;

--
-- Name: ck_category_list; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE ck_category_list (
    category_id integer,
    cate_name text,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.ck_category_list OWNER TO yournet;

--
-- Name: ck_category_list_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE ck_category_list_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ck_category_list_id_seq OWNER TO yournet;

--
-- Name: ck_check_action; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE ck_check_action (
    ckaction_id integer,
    section_id text,
    ckset_id integer,
    employee_id integer,
    action_date date,
    add_date timestamp without time zone,
    status character(1),
    com text
);


ALTER TABLE public.ck_check_action OWNER TO yournet;

--
-- Name: ck_check_action_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE ck_check_action_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ck_check_action_id_seq OWNER TO yournet;

--
-- Name: ck_check_action_list; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE ck_check_action_list (
    ckaction_list_id integer,
    ckaction_id integer,
    section_id text,
    item_id integer,
    action integer,
    com text,
    add_date timestamp without time zone,
    status character(1),
    photo json
);


ALTER TABLE public.ck_check_action_list OWNER TO yournet;

--
-- Name: ck_check_action_list_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE ck_check_action_list_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ck_check_action_list_id_seq OWNER TO yournet;

--
-- Name: ck_check_set; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE ck_check_set (
    ckset_id integer,
    section_id text,
    subject_list text[],
    last_flag integer,
    add_date timestamp without time zone,
    status character(1),
    allot_list integer[]
);


ALTER TABLE public.ck_check_set OWNER TO yournet;

--
-- Name: ck_check_set_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE ck_check_set_id_seq
    START WITH 11
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ck_check_set_id_seq OWNER TO yournet;

--
-- Name: ck_item_list; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE ck_item_list (
    item_id integer,
    category_id integer,
    item_name text,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.ck_item_list OWNER TO yournet;

--
-- Name: ck_item_list_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE ck_item_list_id_seq
    START WITH 101
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ck_item_list_id_seq OWNER TO yournet;

--
-- Name: ck_reply; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE ck_reply (
    reply_id integer,
    ckaction_list_id integer,
    ckaction_id integer,
    section_id text,
    reply text,
    employee_id integer,
    add_date timestamp without time zone,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.ck_reply OWNER TO yournet;

--
-- Name: ck_reply_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE ck_reply_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ck_reply_id_seq OWNER TO yournet;

--
-- Name: ck_viewlog; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE ck_viewlog (
    viewlog_id integer,
    ckaction_id integer,
    section_id text,
    employee_id integer,
    add_date timestamp without time zone,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.ck_viewlog OWNER TO yournet;

--
-- Name: ck_viewlog_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE ck_viewlog_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ck_viewlog_id_seq OWNER TO yournet;

--
-- Name: common_category; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE common_category (
    category_id integer,
    category_name text,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.common_category OWNER TO yournet;

--
-- Name: common_category_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE common_category_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.common_category_id_seq OWNER TO yournet;

--
-- Name: common_info; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE common_info (
    common_id integer,
    consult_id integer,
    campaney_id integer,
    category_id integer,
    employee_id integer,
    tabs integer[],
    title text,
    mess text,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.common_info OWNER TO yournet;

--
-- Name: common_info_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE common_info_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.common_info_id_seq OWNER TO yournet;

--
-- Name: common_tab; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE common_tab (
    tab_id integer,
    tab_name text,
    kind integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.common_tab OWNER TO yournet;

--
-- Name: common_tab_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE common_tab_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.common_tab_id_seq OWNER TO yournet;

--
-- Name: consult; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE consult (
    consult_id integer,
    campaney_id integer,
    parent_id integer,
    type character(1),
    send_id integer,
    return_id integer,
    r_flag character(1),
    title text,
    mess text,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.consult OWNER TO yournet;

--
-- Name: consult_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE consult_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.consult_id_seq OWNER TO yournet;

--
-- Name: consult_receive; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE consult_receive (
    consult_receive_id integer,
    add_date timestamp without time zone
);


ALTER TABLE public.consult_receive OWNER TO yournet;

--
-- Name: dashboard; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE dashboard (
    dashboard_id integer,
    employee_id integer,
    menu_id integer,
    cate integer,
    cate_name text,
    jun integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.dashboard OWNER TO yournet;

--
-- Name: dashboard_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE dashboard_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dashboard_id_seq OWNER TO yournet;

--
-- Name: date_weather; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE date_weather (
    id integer NOT NULL,
    weather text,
    date date,
    timezone_status smallint
);


ALTER TABLE public.date_weather OWNER TO yournet;

--
-- Name: date_weather_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE date_weather_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.date_weather_id_seq OWNER TO yournet;

--
-- Name: date_weather_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: yournet
--

ALTER SEQUENCE date_weather_id_seq OWNED BY date_weather.id;


--
-- Name: desc_comment; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE desc_comment (
    shop_id integer NOT NULL,
    date date NOT NULL,
    revenue_comment text,
    food_purchase_comment text,
    labor_cost_comment text,
    other_comment text
);


ALTER TABLE public.desc_comment OWNER TO yournet;

--
-- Name: docu_manage; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE docu_manage (
    file_id integer,
    company_id integer,
    kind integer,
    parent_id integer,
    type character(1),
    displayname text,
    filename text,
    employee_id integer,
    section_auth json,
    employee_type_auth json,
    position_class_auth json,
    sortno integer,
    add_date timestamp without time zone,
    status character(1),
    ext text
);


ALTER TABLE public.docu_manage OWNER TO yournet;

--
-- Name: docu_manage_file_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE docu_manage_file_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.docu_manage_file_id_seq OWNER TO yournet;

--
-- Name: employee_in_data; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE employee_in_data (
    employee_in_data_id integer,
    employee_num text,
    employee_name text,
    kana_name text,
    sex character(1),
    birthday date,
    indate date,
    company_id integer,
    section_id text,
    employee_type integer,
    position_name text,
    position_class integer,
    email text,
    add_date timestamp without time zone,
    comp_status character(1)
);


ALTER TABLE public.employee_in_data OWNER TO yournet;

--
-- Name: employee_in_data_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE employee_in_data_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.employee_in_data_id_seq OWNER TO yournet;

--
-- Name: employee_list; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE employee_list (
    employee_id integer,
    employee_num text,
    employee_name text,
    kana_name text,
    sex character(1),
    birthday date,
    indate date,
    company_id integer,
    section_id text,
    employee_type integer,
    position_name text,
    position_class integer,
    view_auth json,
    edit_auth json,
    admin_auth integer,
    pass text,
    email text,
    add_date timestamp without time zone,
    status character(1),
    nodel_flag integer
);


ALTER TABLE public.employee_list OWNER TO yournet;

--
-- Name: employee_list_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE employee_list_id_seq
    START WITH 10001
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.employee_list_id_seq OWNER TO yournet;

--
-- Name: employee_list_tmp; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE employee_list_tmp (
    employee_id_tmp integer,
    employee_num text,
    employee_name text,
    kana_name text,
    sex character(1),
    birthday date,
    indate date,
    company_id integer,
    section_id text,
    employee_type integer,
    position_name text,
    position_class integer,
    view_auth json,
    edit_auth json,
    admin_auth integer,
    pass text,
    email text,
    add_date timestamp without time zone,
    status character(1),
    nodel_flag integer
);


ALTER TABLE public.employee_list_tmp OWNER TO yournet;

--
-- Name: employee_list_tmp_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE employee_list_tmp_id_seq
    START WITH 10001
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.employee_list_tmp_id_seq OWNER TO yournet;

--
-- Name: expense_list; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE expense_list (
    code integer NOT NULL,
    name text,
    type integer,
    yudai_code text
);


ALTER TABLE public.expense_list OWNER TO yournet;

--
-- Name: fd_cost; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE fd_cost (
    shop_id text NOT NULL,
    date date NOT NULL,
    food_revenue bigint,
    food_beginning_inventory bigint,
    food_inventory bigint,
    food_end_inventory bigint,
    drink_revenue bigint,
    drink_beginning_inventory bigint,
    drink_inventory bigint,
    drink_end_inventory bigint
);


ALTER TABLE public.fd_cost OWNER TO yournet;

--
-- Name: forget; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE forget (
    forget_id integer,
    type character(1),
    user_id integer,
    onetime_pass text,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.forget OWNER TO yournet;

--
-- Name: forget_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE forget_id_seq
    START WITH 1001
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.forget_id_seq OWNER TO yournet;

--
-- Name: infomart_inventory; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE infomart_inventory (
    id integer NOT NULL,
    shop_id text NOT NULL,
    date date,
    beginning_inventory bigint,
    end_inventory bigint,
    inventory bigint
);


ALTER TABLE public.infomart_inventory OWNER TO yournet;

--
-- Name: infomart_inventory_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE infomart_inventory_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.infomart_inventory_id_seq OWNER TO yournet;

--
-- Name: infomart_inventory_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: yournet
--

ALTER SEQUENCE infomart_inventory_id_seq OWNED BY infomart_inventory.id;


--
-- Name: kpi_managements; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE kpi_managements (
    id integer NOT NULL,
    shop_id bigint NOT NULL,
    cost_rate_achievement double precision,
    overtime_hours double precision,
    ms_score double precision,
    survey_acquisition_rate double precision,
    survey_overall double precision,
    number_of_complaints integer,
    sanitation_inspection_rate double precision,
    sanitation_inspection_evaluate text,
    proactive_approach double precision,
    compliance_with_rules double precision,
    data_date date NOT NULL,
    value_type integer,
    profit_target integer,
    cost_target integer,
    number_of_customer_target integer,
    labor_cost_target double precision,
    man_hour_sales_target double precision
);


ALTER TABLE public.kpi_managements OWNER TO yournet;

--
-- Name: kpi_managements_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE kpi_managements_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.kpi_managements_id_seq OWNER TO yournet;

--
-- Name: kpi_managements_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: yournet
--

ALTER SEQUENCE kpi_managements_id_seq OWNED BY kpi_managements.id;


--
-- Name: labor_cost; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE labor_cost (
    shop_id integer,
    date date,
    direct_labor_cost bigint,
    indirect_labor_cost bigint
);


ALTER TABLE public.labor_cost OWNER TO yournet;

--
-- Name: lunch_dinner; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE lunch_dinner (
    lunch_dinner_id integer,
    shop_id text,
    sale_date date,
    ld_type integer,
    sale_notax_price integer,
    sale_score integer,
    num_custom integer,
    num_pair integer,
    discount_notax integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.lunch_dinner OWNER TO yournet;

--
-- Name: lunch_dinner_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE lunch_dinner_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.lunch_dinner_id_seq OWNER TO yournet;

--
-- Name: lunch_time; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE lunch_time (
    shop_id integer,
    set_no integer,
    from_set date,
    to_set date,
    from_lunch text,
    to_lunch text,
    from_dinner text,
    to_dinner text,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.lunch_time OWNER TO yournet;

--
-- Name: magnet_ptcard_log; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE magnet_ptcard_log (
    ptlog_id integer,
    shop_id integer,
    devicecode text,
    record_time timestamp without time zone,
    member_no text,
    sales integer,
    sale_point integer,
    w_point integer,
    count_point integer,
    special_point integer,
    free_point integer,
    total_point integer,
    exchange_point integer,
    use_count integer,
    last_use_date date,
    add_date timestamp without time zone,
    check_code integer,
    pos_ttotal_id integer,
    other_pos_ttotal_id integer[]
);


ALTER TABLE public.magnet_ptcard_log OWNER TO yournet;

--
-- Name: magnet_ptcard_log_ptlog_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE magnet_ptcard_log_ptlog_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.magnet_ptcard_log_ptlog_id_seq OWNER TO yournet;

--
-- Name: mail_send; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE mail_send (
    employee_id integer,
    cate integer,
    config character(1),
    up_date timestamp without time zone
);


ALTER TABLE public.mail_send OWNER TO yournet;

--
-- Name: man_hour; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE man_hour (
    id integer NOT NULL,
    date timestamp without time zone,
    shop_id integer,
    target_man_hour bigint
);


ALTER TABLE public.man_hour OWNER TO yournet;

--
-- Name: man_hour_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE man_hour_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.man_hour_id_seq OWNER TO yournet;

--
-- Name: man_hour_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: yournet
--

ALTER SEQUENCE man_hour_id_seq OWNED BY man_hour.id;


--
-- Name: manage_money_check; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE manage_money_check (
    manage_money_check_id integer,
    pos_shopno integer,
    sale_date date,
    checktype integer,
    employee_id integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.manage_money_check OWNER TO yournet;

--
-- Name: manage_money_check_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE manage_money_check_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.manage_money_check_id_seq OWNER TO yournet;

--
-- Name: manager_meeting; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE manager_meeting (
    date date NOT NULL,
    shop_id text NOT NULL,
    labor_cost bigint,
    purchase_cost bigint,
    drink_cost bigint
);


ALTER TABLE public.manager_meeting OWNER TO yournet;

--
-- Name: manager_pdca; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE manager_pdca (
    pdca_id integer,
    shop_id integer,
    month date,
    pre_pdca_id integer,
    purpose_id integer,
    attack text,
    goal text,
    charge integer,
    result text,
    analysis text,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.manager_pdca OWNER TO yournet;

--
-- Name: manager_pdca_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE manager_pdca_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.manager_pdca_id_seq OWNER TO yournet;

--
-- Name: menu; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE menu (
    menu_id integer,
    menu_name text,
    menu_class text,
    menu_sub text,
    link text,
    belong_id integer,
    campaney_list integer[],
    section_list text[],
    type_list integer[],
    position_list integer[],
    admin_list integer[],
    list_no integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.menu OWNER TO yournet;

--
-- Name: menu_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE menu_id_seq
    START WITH 100
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.menu_id_seq OWNER TO yournet;

--
-- Name: message; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE message (
    message_id integer,
    employee_id integer,
    title text,
    content text,
    attachment text[],
    attachmentname text[],
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.message OWNER TO yournet;

--
-- Name: message_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE message_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.message_id_seq OWNER TO yournet;

--
-- Name: message_log; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE message_log (
    message_id integer,
    employee_id integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.message_log OWNER TO yournet;

--
-- Name: onsen_budget; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE onsen_budget (
    id integer NOT NULL,
    type smallint,
    date date,
    value bigint
);


ALTER TABLE public.onsen_budget OWNER TO yournet;

--
-- Name: onsen_budget_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE onsen_budget_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.onsen_budget_id_seq OWNER TO yournet;

--
-- Name: onsen_budget_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: yournet
--

ALTER SEQUENCE onsen_budget_id_seq OWNED BY onsen_budget.id;


--
-- Name: onsen_daily_work_report; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE onsen_daily_work_report (
    id integer NOT NULL,
    date date,
    category_id integer,
    value bigint,
    created_at timestamp without time zone
);


ALTER TABLE public.onsen_daily_work_report OWNER TO yournet;

--
-- Name: onsen_daily_work_report_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE onsen_daily_work_report_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.onsen_daily_work_report_id_seq OWNER TO yournet;

--
-- Name: onsen_daily_work_report_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: yournet
--

ALTER SEQUENCE onsen_daily_work_report_id_seq OWNED BY onsen_daily_work_report.id;


--
-- Name: onsen_report_category; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE onsen_report_category (
    id integer NOT NULL,
    name text
);


ALTER TABLE public.onsen_report_category OWNER TO yournet;

--
-- Name: onsen_report_category_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE onsen_report_category_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.onsen_report_category_id_seq OWNER TO yournet;

--
-- Name: onsen_report_category_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: yournet
--

ALTER SEQUENCE onsen_report_category_id_seq OWNED BY onsen_report_category.id;


--
-- Name: order_main; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE order_main (
    order_id integer,
    order_date date,
    delivery_date date,
    product_id integer[],
    product_other text,
    shop_id integer,
    order_emp_id integer,
    photo_date date,
    photo_place text,
    photo_start timestamp without time zone,
    photo_end timestamp without time zone,
    all_biko text,
    approval integer,
    invoice_date date,
    mg_emp_id integer,
    attachfile text[],
    add_date timestamp without time zone,
    status text,
    delivery_plan_date date,
    sales_emp_id integer
);


ALTER TABLE public.order_main OWNER TO yournet;

--
-- Name: order_main_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE order_main_id_seq
    START WITH 11
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.order_main_id_seq OWNER TO yournet;

--
-- Name: order_main_log; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE order_main_log (
    order_main_log_id integer,
    order_id integer,
    change_emp_id integer,
    add_date timestamp without time zone,
    nex_status text
);


ALTER TABLE public.order_main_log OWNER TO yournet;

--
-- Name: order_main_log_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE order_main_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.order_main_log_id_seq OWNER TO yournet;

--
-- Name: order_shooting; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE order_shooting (
    shooting_id integer,
    order_id integer,
    num integer,
    photo_name text,
    price integer,
    foodst text,
    photo_biko text,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.order_shooting OWNER TO yournet;

--
-- Name: order_shooting_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE order_shooting_id_seq
    START WITH 11
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.order_shooting_id_seq OWNER TO yournet;

--
-- Name: order_tool; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE order_tool (
    tool_id integer,
    order_id integer,
    num integer,
    tool_name text,
    quantity integer,
    delivery text,
    papersize integer,
    toolbiko text,
    add_date timestamp without time zone,
    status character(1),
    tool_emp_id integer,
    laminate character(1)
);


ALTER TABLE public.order_tool OWNER TO yournet;

--
-- Name: order_tool_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE order_tool_id_seq
    START WITH 11
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.order_tool_id_seq OWNER TO yournet;

--
-- Name: pos_intable_check; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_intable_check (
    pos_shopno integer,
    sale_date date,
    table_id integer,
    row_num integer,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_intable_check OWNER TO yournet;

--
-- Name: pos_titem; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_titem (
    pos_titem_id integer,
    pos_shopno integer,
    sale_date date,
    receipt_no integer,
    menu_kind text,
    process_type1 integer,
    process_type2 integer,
    menu_code text,
    menu_name text,
    slip_no integer,
    table_no text,
    order_time text,
    parent_menu_code text,
    parent_menu_name text,
    own_code text,
    sale_link_code integer,
    takeout_flag text,
    service_fee2_flag text,
    st1 integer,
    st2 integer,
    st3 integer,
    st4 integer,
    unit_prince integer,
    volume integer,
    remain_volume integer,
    total_price integer,
    unit_cost integer,
    linkdp_code integer,
    linkgp_code integer,
    tax_type integer,
    np_flag text,
    pub_pos_shopno text,
    status_position integer,
    status_no integer,
    comment_no integer,
    discount_score integer,
    discount_price integer,
    promotion_num integer,
    cancel_no integer,
    discount_pay_num integer,
    service_fee1_flag text,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_titem OWNER TO yournet;

--
-- Name: pos_titem_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_titem_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_titem_id_seq OWNER TO yournet;

--
-- Name: pos_tslip; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_tslip (
    pos_tslip_id integer,
    pos_shopno integer,
    sale_date date,
    receipt_no integer,
    slip_no integer,
    table_no text,
    new_order_time text,
    add_order_time text,
    offer_time text,
    floor_no integer,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_tslip OWNER TO yournet;

--
-- Name: pos_tslip_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_tslip_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_tslip_id_seq OWNER TO yournet;

--
-- Name: pos_ttend; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_ttend (
    pos_ttend_id integer,
    pos_shopno integer,
    sale_date date,
    receipt_no integer,
    media_type integer,
    media_name text,
    type1 integer,
    type2 integer,
    type_detail integer,
    note_code integer,
    note_item_name text,
    pub_pos_shopno integer,
    discount_type integer,
    discount_status integer,
    discount_target_price integer,
    unit_prince integer,
    pay_sheet integer,
    remnant_sheet integer,
    deposit_price integer,
    pay_price integer,
    change_price integer,
    change_type integer,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_ttend OWNER TO yournet;

--
-- Name: pos_ttend_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_ttend_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_ttend_id_seq OWNER TO yournet;

--
-- Name: pos_ttotal; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_ttotal (
    pos_ttotal_id integer,
    pos_shopno integer,
    sale_date date,
    receipt_no integer,
    transaction_type integer,
    void_receipt_no integer,
    handler_no integer,
    charger_no integer,
    old_handler_no integer,
    old_charger_no integer,
    accounting_type integer,
    slip_no integer,
    table_no text,
    slip_branch_no integer,
    slip_series_no integer,
    new_order_time text,
    add_order_time text,
    offer_time text,
    pay_time text,
    num_pair integer,
    pos_num integer,
    order_num integer,
    pay_num integer,
    free1_code integer,
    free1_name text,
    free2_code integer,
    free2_name text,
    free3_code integer,
    free3_name text,
    restaurant_custom integer,
    front_custom integer,
    sale_custom integer,
    income_custom integer,
    through_num integer,
    copy_number text,
    optional_info2 text,
    optional_info3 text,
    optional_info4 text,
    custom_base1 integer,
    custom_base2 integer,
    custom_base3 integer,
    custom_base4 integer,
    custom_base5 integer,
    dishup_start text,
    dishup_end text,
    item_score integer,
    instore1_notax_price integer,
    instore1_notax_score integer,
    instore2_intax_price integer,
    instore2_intax_score integer,
    instore_outtaxable_price integer,
    instore_outtaxable_score integer,
    instore_intaxable_no_price integer,
    instore_intaxable_no_score integer,
    instore_intaxable_in_price integer,
    instore_intaxable_in_score integer,
    instore_notaxable_price integer,
    instore_notaxable_score integer,
    takeout1_notax_price integer,
    takeout1_notax_score integer,
    takeout2_intax_price integer,
    takeout2_intax_score integer,
    takeout_outtaxable_price integer,
    takeout_outtaxable_score integer,
    takeout_intaxable_no_price integer,
    takeout_intaxable_no_score integer,
    takeout_intaxable_in_price integer,
    takeout_intaxable_in_score integer,
    takeout_notaxable_price integer,
    takeout_notaxable_score integer,
    restaurant1_notax_price integer,
    restaurant1_notax_score integer,
    restaurant2_intax_price integer,
    restaurant2_intax_score integer,
    restaurant_outtaxable_price integer,
    restaurant_outtaxable_score integer,
    restaurant_intaxable_no_price integer,
    restaurant_intaxable_no_score integer,
    restaurant_intaxable_in_price integer,
    restaurant_intaxable_in_score integer,
    restaurant_notaxable_price integer,
    restaurant_notaxable_score integer,
    front1_notax_price integer,
    front1_notax_score integer,
    front2_intax_price integer,
    front2_intax_score integer,
    front_outtaxable_price integer,
    front_outtaxable_score integer,
    front_intaxable_no_price integer,
    front_intaxable_no_score integer,
    front_intaxable_in_price integer,
    front_intaxable_in_score integer,
    front_notaxable_price integer,
    front_notaxable_score integer,
    servicefee1_price integer,
    servicefee1_score integer,
    servicefee1_pair integer,
    servicefee1_custom integer,
    servicefee2_price integer,
    servicefee2_score integer,
    servicefee2_pair integer,
    servicefee2_custom integer,
    seatfee_price integer,
    seatfee_score integer,
    seatfee_pair integer,
    seatfee_custom integer,
    disfrant_price integer,
    totalsale1_notax_price integer,
    totalsale1_notax_score integer,
    totalsale2_intax_price integer,
    totalsale2_intax_score integer,
    totalsale3_notax_price integer,
    totalsale3_notax_score integer,
    totalsale4_intax_price integer,
    totalsale4_intax_score integer,
    income1_notax_price integer,
    income2_intax_price integer,
    income3_notax_price integer,
    income4_intax_price integer,
    total_pay1_notax_price integer,
    total_pay2_intax_price integer,
    notesale_price integer,
    notesale_count integer,
    notesale_sheet integer,
    out_tax integer,
    in_tax integer,
    out_taxable_price integer,
    in_taxable_price integer,
    no_taxable_price integer,
    billing_price integer,
    servicefee1_rate integer,
    servicefee2_rate integer,
    taxation1_type integer,
    taxation1_rate integer,
    taxation2_type integer,
    taxation2_rate integer,
    taxation3_type integer,
    taxation3_rate integer,
    taxation4_type integer,
    taxation4_rate integer,
    taxation5_type integer,
    taxation5_rate integer,
    ten_thousand_num integer,
    weather1 integer,
    weather2 integer,
    weather3 integer,
    weather4 integer,
    weather5 integer,
    weather6 integer,
    temperature1 integer,
    temperature2 integer,
    temperature3 integer,
    temperature4 integer,
    temperature5 integer,
    temperature6 integer,
    receipt_no_from integer,
    receipt_no_to integer,
    unsettle_pair integer,
    unsettle_price integer,
    change_reserve integer,
    shift_settle_num integer,
    settle_time text,
    totalsale5_outtax_price integer,
    discount_price integer,
    discount_notax_price integer,
    business_date text,
    audit_item_num integer,
    pay_item_num integer,
    custom_code integer,
    pt_card_before integer,
    pt_card_add integer,
    pt_card_cumulate integer,
    pt_card_fullsubtract integer,
    pt_card_issue integer,
    pt_card_paysubtract integer,
    pt_card_memberno integer,
    servicefee_flag text,
    revenue_flag integer,
    floor_no integer,
    pt_slip_no text,
    this_point integer,
    cumulate_point integer,
    point_unit text,
    point_memberno text,
    point_notax_price integer,
    special_sale_flag text,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_ttotal OWNER TO yournet;

--
-- Name: pos_ttotal_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_ttotal_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_ttotal_id_seq OWNER TO yournet;

--
-- Name: pos_zaudit; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_zaudit (
    pos_zaudit_id integer,
    pos_shopno integer,
    sale_date date,
    audit_class integer,
    audit_name text,
    receipt_no integer,
    slip_no integer,
    sequence_no integer,
    charger_no integer,
    charger_name text,
    handler_no integer,
    handler_name text,
    check_time text,
    sum_price integer,
    old_charger_no integer,
    old_charger_name text,
    old_handler_no integer,
    old_handler_name text,
    reason_code integer,
    void_receipt_no integer,
    void_time text,
    menu_code text,
    menu_name text,
    menu_cate integer,
    menu_cate_name text,
    unit_prince integer,
    volume integer,
    np_flag integer,
    own_code text,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_zaudit OWNER TO yournet;

--
-- Name: pos_zaudit_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_zaudit_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_zaudit_id_seq OWNER TO yournet;

--
-- Name: pos_zdealer; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_zdealer (
    pos_zdealer_id integer,
    pos_shopno integer,
    sale_date date,
    operator_code integer,
    employee_code integer,
    employee_name text,
    total_no integer,
    start_time integer,
    end_time integer,
    instore1_notax_price integer,
    instore1_notax_score integer,
    instore2_intax_price integer,
    instore2_intax_score integer,
    takeout1_notax_price integer,
    takeout1_notax_score integer,
    takeout2_intax_price integer,
    takeout2_intax_score integer,
    restaurant1_notax_price integer,
    restaurant1_notax_score integer,
    restaurant2_intax_price integer,
    restaurant2_intax_score integer,
    front1_notax_price integer,
    front1_notax_score integer,
    front2_intax_price integer,
    front2_intax_score integer,
    servicefee1_price integer,
    servicefee1_score integer,
    servicefee1_pair integer,
    servicefee1_custom integer,
    servicefee2_price integer,
    servicefee2_score integer,
    servicefee2_pair integer,
    servicefee2_custom integer,
    seatfee_price integer,
    seatfee_score integer,
    seatfee_pair integer,
    seatfee_custom integer,
    totalsale1_notax_price integer,
    totalsale1_notax_score integer,
    totalsale2_intax_price integer,
    totalsale2_intax_score integer,
    totalsale3_notax_price integer,
    totalsale3_notax_score integer,
    totalsale4_intax_price integer,
    totalsale4_intax_score integer,
    discount_price integer,
    discount_count integer,
    discount_sheet integer,
    disdiff_price integer,
    disdiff_count integer,
    disfrant_price integer,
    disfrant_count integer,
    num_custom integer,
    num_pair integer,
    notesale_price integer,
    notesale_count integer,
    notesale_sheet integer,
    out_tax integer,
    in_tax integer,
    deposit_price integer,
    deposit_count integer,
    income1_notax_price integer,
    income2_intax_price integer,
    income3_notax_price integer,
    income4_intax_price integer,
    discount_notax_price integer,
    totalsale5_outtax_price integer,
    totalsale5_outtax_score integer,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_zdealer OWNER TO yournet;

--
-- Name: pos_zdealer_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_zdealer_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_zdealer_id_seq OWNER TO yournet;

--
-- Name: pos_zfloor; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_zfloor (
    pos_zfloor_id integer,
    pos_shopno integer,
    sale_date date,
    floor_no integer,
    floor_name text,
    total_no integer,
    start_time integer,
    end_time integer,
    instore1_notax_price integer,
    instore1_notax_score integer,
    instore2_intax_price integer,
    instore2_intax_score integer,
    takeout1_notax_price integer,
    takeout1_notax_score integer,
    takeout2_intax_price integer,
    takeout2_intax_score integer,
    restaurant1_notax_price integer,
    restaurant1_notax_score integer,
    restaurant2_intax_price integer,
    restaurant2_intax_score integer,
    front1_notax_price integer,
    front1_notax_score integer,
    front2_intax_price integer,
    front2_intax_score integer,
    servicefee1_price integer,
    servicefee1_score integer,
    servicefee1_pair integer,
    servicefee1_custom integer,
    servicefee2_price integer,
    servicefee2_score integer,
    servicefee2_pair integer,
    servicefee2_custom integer,
    seatfee_price integer,
    seatfee_score integer,
    seatfee_pair integer,
    seatfee_custom integer,
    totalsale1_notax_price integer,
    totalsale1_notax_score integer,
    totalsale2_intax_price integer,
    totalsale2_intax_score integer,
    totalsale3_notax_price integer,
    totalsale3_notax_score integer,
    totalsale4_intax_price integer,
    totalsale4_intax_score integer,
    discount_price integer,
    discount_count integer,
    discount_sheet integer,
    disdiff_price integer,
    disdiff_count integer,
    disfrant_price integer,
    disfrant_count integer,
    num_custom integer,
    num_pair integer,
    notesale_price integer,
    notesale_count integer,
    notesale_sheet integer,
    out_tax integer,
    in_tax integer,
    deposit_price integer,
    deposit_count integer,
    income1_notax_price integer,
    income2_intax_price integer,
    income3_notax_price integer,
    income4_intax_price integer,
    discount_notax_price integer,
    totalsale5_outtax_price integer,
    totalsale5_outtax_score integer,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_zfloor OWNER TO yournet;

--
-- Name: pos_zfloor_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_zfloor_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_zfloor_id_seq OWNER TO yournet;

--
-- Name: pos_zfree; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_zfree (
    pos_zfree_id integer,
    pos_shopno integer,
    sale_date date,
    free_class integer,
    free_code integer,
    free_code_name text,
    total_no integer,
    start_time integer,
    end_time integer,
    instore1_notax_price integer,
    instore1_notax_score integer,
    instore2_intax_price integer,
    instore2_intax_score integer,
    takeout1_notax_price integer,
    takeout1_notax_score integer,
    takeout2_intax_price integer,
    takeout2_intax_score integer,
    restaurant1_notax_price integer,
    restaurant1_notax_score integer,
    restaurant2_intax_price integer,
    restaurant2_intax_score integer,
    front1_notax_price integer,
    front1_notax_score integer,
    front2_intax_price integer,
    front2_intax_score integer,
    servicefee1_price integer,
    servicefee1_score integer,
    servicefee1_pair integer,
    servicefee1_custom integer,
    servicefee2_price integer,
    servicefee2_score integer,
    servicefee2_pair integer,
    servicefee2_custom integer,
    seatfee_price integer,
    seatfee_score integer,
    seatfee_pair integer,
    seatfee_custom integer,
    totalsale1_notax_price integer,
    totalsale1_notax_score integer,
    totalsale2_intax_price integer,
    totalsale2_intax_score integer,
    totalsale3_notax_price integer,
    totalsale3_notax_score integer,
    totalsale4_intax_price integer,
    totalsale4_intax_score integer,
    discount_price integer,
    discount_count integer,
    discount_sheet integer,
    disdiff_price integer,
    disdiff_count integer,
    disfrant_price integer,
    disfrant_count integer,
    num_custom integer,
    num_pair integer,
    notesale_price integer,
    notesale_count integer,
    notesale_sheet integer,
    out_tax integer,
    in_tax integer,
    deposit_price integer,
    deposit_count integer,
    income1_notax_price integer,
    income2_intax_price integer,
    income3_notax_price integer,
    income4_intax_price integer,
    discount_notax_price integer,
    totalsale5_outtax_price integer,
    totalsale5_outtax_score integer,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_zfree OWNER TO yournet;

--
-- Name: pos_zfree_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_zfree_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_zfree_id_seq OWNER TO yournet;

--
-- Name: pos_zfuse; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_zfuse (
    pos_zfuse_id integer,
    pos_shopno integer,
    sale_date date,
    order_emp_code integer,
    employee_code integer,
    employee_name text,
    total_no integer,
    start_time integer,
    end_time integer,
    order_count integer,
    order_custom integer,
    order_price integer,
    cancel_order_count integer,
    cancel_order_custom integer,
    cancel_order_price integer,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_zfuse OWNER TO yournet;

--
-- Name: pos_zfuse_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_zfuse_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_zfuse_id_seq OWNER TO yournet;

--
-- Name: pos_zitem; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_zitem (
    pos_zitem_id integer,
    pos_shopno integer,
    sale_date date,
    menu_code text,
    own_code text,
    menu_name text,
    menu_cate integer,
    menu_cate_name text,
    parent_menu_code text,
    parent_menu_name text,
    unit_prince integer,
    free1_code integer,
    free1_name text,
    free2_code integer,
    free2_name text,
    free3_code integer,
    free3_name text,
    takeout_class integer,
    takeout_class_name text,
    unit_cost integer,
    gp_code integer,
    gp_name text,
    dp_code integer,
    dp_name text,
    timezone01_score integer,
    timezone02_score integer,
    timezone03_score integer,
    timezone04_score integer,
    timezone05_score integer,
    timezone06_score integer,
    timezone07_score integer,
    timezone08_score integer,
    timezone09_score integer,
    timezone10_score integer,
    timezone11_score integer,
    timezone12_score integer,
    timezone13_score integer,
    timezone14_score integer,
    timezone15_score integer,
    timezone16_score integer,
    timezone17_score integer,
    timezone18_score integer,
    timezone19_score integer,
    timezone20_score integer,
    timezone21_score integer,
    timezone22_score integer,
    timezone23_score integer,
    timezone24_score integer,
    employ_num integer,
    promotion_num integer,
    product_data1 integer,
    product_data2 integer,
    product_data3 integer,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_zitem OWNER TO yournet;

--
-- Name: pos_zitem_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_zitem_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_zitem_id_seq OWNER TO yournet;

--
-- Name: pos_zkkak; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_zkkak (
    pos_zkkak_id integer,
    pos_shopno integer,
    sale_date date,
    price_range integer,
    price_range_start integer,
    price_range_end integer,
    total_no integer,
    start_time integer,
    end_time integer,
    instore1_notax_price integer,
    instore1_notax_score integer,
    instore2_intax_price integer,
    instore2_intax_score integer,
    takeout1_notax_price integer,
    takeout1_notax_score integer,
    takeout2_intax_price integer,
    takeout2_intax_score integer,
    restaurant1_notax_price integer,
    restaurant1_notax_score integer,
    restaurant2_intax_price integer,
    restaurant2_intax_score integer,
    front1_notax_price integer,
    front1_notax_score integer,
    front2_intax_price integer,
    front2_intax_score integer,
    servicefee1_price integer,
    servicefee1_score integer,
    servicefee1_pair integer,
    servicefee1_custom integer,
    servicefee2_price integer,
    servicefee2_score integer,
    servicefee2_pair integer,
    servicefee2_custom integer,
    seatfee_price integer,
    seatfee_score integer,
    seatfee_pair integer,
    seatfee_custom integer,
    totalsale1_notax_price integer,
    totalsale1_notax_score integer,
    totalsale2_intax_price integer,
    totalsale2_intax_score integer,
    totalsale3_notax_price integer,
    totalsale3_notax_score integer,
    totalsale4_intax_price integer,
    totalsale4_intax_score integer,
    discount_price integer,
    discount_count integer,
    discount_sheet integer,
    disdiff_price integer,
    disdiff_count integer,
    disfrant_price integer,
    disfrant_count integer,
    num_custom integer,
    num_pair integer,
    notesale_price integer,
    notesale_count integer,
    notesale_sheet integer,
    out_tax integer,
    in_tax integer,
    deposit_price integer,
    deposit_count integer,
    income1_notax_price integer,
    income2_intax_price integer,
    income3_notax_price integer,
    income4_intax_price integer,
    discount_notax_price integer,
    totalsale5_outtax_price integer,
    totalsale5_outtax_score integer,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_zkkak OWNER TO yournet;

--
-- Name: pos_zkkak_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_zkkak_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_zkkak_id_seq OWNER TO yournet;

--
-- Name: pos_zkkum; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_zkkum (
    pos_zkkum_id integer,
    pos_shopno integer,
    sale_date date,
    pair_num integer,
    pair_start integer,
    pair_end integer,
    total_no integer,
    start_time integer,
    end_time integer,
    instore1_notax_price integer,
    instore1_notax_score integer,
    instore2_intax_price integer,
    instore2_intax_score integer,
    takeout1_notax_price integer,
    takeout1_notax_score integer,
    takeout2_intax_price integer,
    takeout2_intax_score integer,
    restaurant1_notax_price integer,
    restaurant1_notax_score integer,
    restaurant2_intax_price integer,
    restaurant2_intax_score integer,
    front1_notax_price integer,
    front1_notax_score integer,
    front2_intax_price integer,
    front2_intax_score integer,
    servicefee1_price integer,
    servicefee1_score integer,
    servicefee1_pair integer,
    servicefee1_custom integer,
    servicefee2_price integer,
    servicefee2_score integer,
    servicefee2_pair integer,
    servicefee2_custom integer,
    seatfee_price integer,
    seatfee_score integer,
    seatfee_pair integer,
    seatfee_custom integer,
    totalsale1_notax_price integer,
    totalsale1_notax_score integer,
    totalsale2_intax_price integer,
    totalsale2_intax_score integer,
    totalsale3_notax_price integer,
    totalsale3_notax_score integer,
    totalsale4_intax_price integer,
    totalsale4_intax_score integer,
    discount_price integer,
    discount_count integer,
    discount_sheet integer,
    disdiff_price integer,
    disdiff_count integer,
    disfrant_price integer,
    disfrant_count integer,
    num_custom integer,
    num_pair integer,
    notesale_price integer,
    notesale_count integer,
    notesale_sheet integer,
    out_tax integer,
    in_tax integer,
    deposit_price integer,
    deposit_count integer,
    income1_notax_price integer,
    income2_intax_price integer,
    income3_notax_price integer,
    income4_intax_price integer,
    discount_notax_price integer,
    totalsale5_outtax_price integer,
    totalsale5_outtax_score integer,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_zkkum OWNER TO yournet;

--
-- Name: pos_zkkum_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_zkkum_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_zkkum_id_seq OWNER TO yournet;

--
-- Name: pos_ztair; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_ztair (
    pos_ztair_id integer,
    pos_shopno integer,
    sale_date date,
    detent_time integer,
    detent_start integer,
    detent_end integer,
    total_no integer,
    start_time integer,
    end_time integer,
    instore1_notax_price integer,
    instore1_notax_score integer,
    instore2_intax_price integer,
    instore2_intax_score integer,
    takeout1_notax_price integer,
    takeout1_notax_score integer,
    takeout2_intax_price integer,
    takeout2_intax_score integer,
    restaurant1_notax_price integer,
    restaurant1_notax_score integer,
    restaurant2_intax_price integer,
    restaurant2_intax_score integer,
    front1_notax_price integer,
    front1_notax_score integer,
    front2_intax_price integer,
    front2_intax_score integer,
    servicefee1_price integer,
    servicefee1_score integer,
    servicefee1_pair integer,
    servicefee1_custom integer,
    servicefee2_price integer,
    servicefee2_score integer,
    servicefee2_pair integer,
    servicefee2_custom integer,
    seatfee_price integer,
    seatfee_score integer,
    seatfee_pair integer,
    seatfee_custom integer,
    totalsale1_notax_price integer,
    totalsale1_notax_score integer,
    totalsale2_intax_price integer,
    totalsale2_intax_score integer,
    totalsale3_notax_price integer,
    totalsale3_notax_score integer,
    totalsale4_intax_price integer,
    totalsale4_intax_score integer,
    discount_price integer,
    discount_count integer,
    discount_sheet integer,
    disdiff_price integer,
    disdiff_count integer,
    disfrant_price integer,
    disfrant_count integer,
    num_custom integer,
    num_pair integer,
    notesale_price integer,
    notesale_count integer,
    notesale_sheet integer,
    out_tax integer,
    in_tax integer,
    deposit_price integer,
    deposit_count integer,
    income1_notax_price integer,
    income2_intax_price integer,
    income3_notax_price integer,
    income4_intax_price integer,
    discount_notax_price integer,
    totalsale5_outtax_price integer,
    totalsale5_outtax_score integer,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_ztair OWNER TO yournet;

--
-- Name: pos_ztair_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_ztair_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_ztair_id_seq OWNER TO yournet;

--
-- Name: pos_zticket; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_zticket (
    pos_zticket_id integer,
    pos_shopno integer,
    sale_date date,
    ticket_flag integer,
    ticket_flag_name text,
    ticket_code integer,
    pub_pos_shopno integer,
    ticket_price integer,
    discount_status integer,
    ticket_name text,
    sheet integer,
    score integer,
    sum_price integer,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_zticket OWNER TO yournet;

--
-- Name: pos_zticket_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_zticket_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_zticket_id_seq OWNER TO yournet;

--
-- Name: pos_ztime; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_ztime (
    pos_ztime_id integer,
    pos_shopno integer,
    sale_date date,
    ttype integer,
    total_no integer,
    start_time integer,
    end_time integer,
    instore1_notax_price integer,
    instore1_notax_score integer,
    instore2_intax_price integer,
    instore2_intax_score integer,
    takeout1_notax_price integer,
    takeout1_notax_score integer,
    takeout2_intax_price integer,
    takeout2_intax_score integer,
    restaurant1_notax_price integer,
    restaurant1_notax_score integer,
    restaurant2_intax_price integer,
    restaurant2_intax_score integer,
    front1_notax_price integer,
    front1_notax_score integer,
    front2_intax_price integer,
    front2_intax_score integer,
    servicefee1_price integer,
    servicefee1_score integer,
    servicefee1_pair integer,
    servicefee1_custom integer,
    servicefee2_price integer,
    servicefee2_score integer,
    servicefee2_pair integer,
    servicefee2_custom integer,
    seatfee_price integer,
    seatfee_score integer,
    seatfee_pair integer,
    seatfee_custom integer,
    totalsale1_notax_price integer,
    totalsale1_notax_score integer,
    totalsale2_intax_price integer,
    totalsale2_intax_score integer,
    totalsale3_notax_price integer,
    totalsale3_notax_score integer,
    totalsale4_intax_price integer,
    totalsale4_intax_score integer,
    discount_price integer,
    discount_count integer,
    discount_sheet integer,
    disdiff_price integer,
    disdiff_count integer,
    disfrant_price integer,
    disfrant_count integer,
    num_custom integer,
    num_pair integer,
    notesale_price integer,
    notesale_count integer,
    notesale_sheet integer,
    out_tax integer,
    in_tax integer,
    deposit_price integer,
    deposit_count integer,
    income1_notax_price integer,
    income2_intax_price integer,
    income3_notax_price integer,
    income4_intax_price integer,
    discount_notax_price integer,
    totalsale5_outtax_price integer,
    totalsale5_outtax_score integer,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_ztime OWNER TO yournet;

--
-- Name: pos_ztime_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_ztime_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_ztime_id_seq OWNER TO yournet;

--
-- Name: pos_ztotal_discount; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_ztotal_discount (
    pos_ztotal_discount_id integer,
    pos_shopno integer,
    sale_date date,
    ztotal_no integer,
    dis_item_name text,
    dis_price integer,
    dis_count integer,
    dis_sheet integer,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_ztotal_discount OWNER TO yournet;

--
-- Name: pos_ztotal_discount_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_ztotal_discount_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_ztotal_discount_id_seq OWNER TO yournet;

--
-- Name: pos_ztotal_note; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_ztotal_note (
    pos_ztotal_note_id integer,
    pos_shopno integer,
    sale_date date,
    ztotal_no integer,
    note_item_name text,
    note_price integer,
    note_count integer,
    note_sheet integer,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_ztotal_note OWNER TO yournet;

--
-- Name: pos_ztotal_note_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_ztotal_note_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_ztotal_note_id_seq OWNER TO yournet;

--
-- Name: pos_ztotal_pay; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_ztotal_pay (
    pos_ztotal_pay_id integer,
    pos_shopno integer,
    sale_date date,
    ztotal_no integer,
    pay_item_name text,
    pay_price integer,
    pay_count integer,
    pay_sheet integer,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_ztotal_pay OWNER TO yournet;

--
-- Name: pos_ztotal_pay_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_ztotal_pay_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_ztotal_pay_id_seq OWNER TO yournet;

--
-- Name: pos_ztotal_sales; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE pos_ztotal_sales (
    pos_ztotal_sales_id integer,
    pos_shopno integer,
    sale_date date,
    allsale_notax integer,
    allsale_intax integer,
    dissale_notax integer,
    dissale_intax integer,
    sale_score integer,
    num_custom integer,
    num_pair integer,
    discount_notax integer,
    discount_intax integer,
    sale_cancel integer,
    from_receipt integer,
    to_receipt integer,
    change_reserve integer,
    pos_cash integer,
    onhand_cash integer,
    over_short integer,
    bank_deposit integer,
    next_change_reserve integer,
    inshopsale_notax integer,
    inshopsale_intax integer,
    takeoutsale_notax integer,
    takeoutsale_intax integer,
    inshopsale_score integer,
    takeoutsale_score integer,
    inshop_custom integer,
    inshop_pair integer,
    takeout_custom integer,
    takeout_pair integer,
    only_inshop_custom integer,
    only_inshop_pair integer,
    only_takeout_custom integer,
    only_takeout_pair integer,
    up_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.pos_ztotal_sales OWNER TO yournet;

--
-- Name: pos_ztotal_sales_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE pos_ztotal_sales_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pos_ztotal_sales_id_seq OWNER TO yournet;

--
-- Name: sales_by_time; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE sales_by_time (
    shop_id integer NOT NULL,
    date date NOT NULL,
    lunch_revenue bigint,
    lunch_customers_num integer,
    lunch_human_time_sales bigint,
    dinner_revenue bigint,
    dinner_customers_num integer,
    dinner_human_time_sales bigint,
    reservation_revenue bigint,
    reservation_customers_num integer,
    reservation_human_time_sales bigint,
    free_revenue bigint,
    free_customers_num integer,
    free_human_time_sales bigint
);


ALTER TABLE public.sales_by_time OWNER TO yournet;

--
-- Name: session; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE session (
    session_id text,
    employee_id integer,
    employee_num text,
    name text,
    sex character(1),
    company_id integer,
    section_id text,
    employee_type integer,
    position_class integer,
    view_auth json,
    edit_auth json,
    admin_auth integer,
    email text,
    limit_time timestamp without time zone,
    add_date timestamp without time zone
);


ALTER TABLE public.session OWNER TO yournet;

--
-- Name: shop_budget_month; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE shop_budget_month (
    id integer NOT NULL,
    shop_id integer,
    month date,
    expense_code integer,
    value integer,
    category integer
);


ALTER TABLE public.shop_budget_month OWNER TO yournet;

--
-- Name: shop_budget_month_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE shop_budget_month_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.shop_budget_month_id_seq OWNER TO yournet;

--
-- Name: shop_budget_month_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: yournet
--

ALTER SEQUENCE shop_budget_month_id_seq OWNED BY shop_budget_month.id;


SET default_with_oids = false;

--
-- Name: shop_list; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE shop_list (
    id integer NOT NULL,
    name text,
    shop_type integer,
    category integer,
    is_new_shop boolean,
    abbreviation text,
    scraping_id text,
    infomart_id text,
    fankuru_id text,
    jinjer_id text,
    is_scraping boolean DEFAULT true,
    display_order smallint DEFAULT 1 NOT NULL
);


ALTER TABLE public.shop_list OWNER TO yournet;

SET default_with_oids = true;

--
-- Name: shop_performance_month; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE shop_performance_month (
    shop_id integer,
    month date,
    expense_code integer,
    value integer
);


ALTER TABLE public.shop_performance_month OWNER TO yournet;

--
-- Name: shop_preliminary_report; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE shop_preliminary_report (
    shop_id integer,
    date date,
    expense_code integer,
    value integer,
    capture_period integer
);


ALTER TABLE public.shop_preliminary_report OWNER TO yournet;

--
-- Name: standard_unit_price; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE standard_unit_price (
    id integer NOT NULL,
    shop_id integer,
    month date,
    value integer DEFAULT 0
);


ALTER TABLE public.standard_unit_price OWNER TO yournet;

--
-- Name: standard_unit_price_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE standard_unit_price_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.standard_unit_price_id_seq OWNER TO yournet;

--
-- Name: standard_unit_price_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: yournet
--

ALTER SEQUENCE standard_unit_price_id_seq OWNED BY standard_unit_price.id;


--
-- Name: store_survey; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE store_survey (
    id integer NOT NULL,
    shop_id text NOT NULL,
    date date NOT NULL,
    all_score real,
    revisit real,
    reception real,
    offer real,
    cuisine real,
    cleanliness real,
    ambience real,
    cost_performance real,
    all_score_comment text,
    revisit_comment text,
    reception_comment text,
    offer_comment text,
    cuisine_comment text,
    cleanliness_comment text
);


ALTER TABLE public.store_survey OWNER TO yournet;

--
-- Name: store_survey_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE store_survey_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.store_survey_id_seq OWNER TO yournet;

--
-- Name: store_survey_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: yournet
--

ALTER SEQUENCE store_survey_id_seq OWNED BY store_survey.id;


--
-- Name: telecom2_action; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom2_action (
    shop_id integer,
    item_id integer,
    month integer,
    day integer,
    employee_id integer,
    dgoal_num integer,
    action_num integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom2_action OWNER TO yournet;

--
-- Name: telecom2_bigitem; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom2_bigitem (
    bigitem_id integer,
    bigitem_name text,
    shop_id integer,
    month integer,
    order_num integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom2_bigitem OWNER TO yournet;

--
-- Name: telecom2_bigitem_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE telecom2_bigitem_id_seq
    START WITH 11
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.telecom2_bigitem_id_seq OWNER TO yournet;

--
-- Name: telecom2_comment; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom2_comment (
    comment_id integer,
    shop_id integer,
    employee_id integer,
    comment text,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom2_comment OWNER TO yournet;

--
-- Name: telecom2_comment_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE telecom2_comment_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.telecom2_comment_id_seq OWNER TO yournet;

--
-- Name: telecom2_goal; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom2_goal (
    shop_id integer,
    item_id integer,
    month integer,
    employee_id integer,
    goal_num integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom2_goal OWNER TO yournet;

--
-- Name: telecom2_goal_day; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom2_goal_day (
    shop_id integer,
    item_id integer,
    month integer,
    day integer,
    dgoal_num integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom2_goal_day OWNER TO yournet;

--
-- Name: telecom2_goal_group; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom2_goal_group (
    shop_id integer,
    item_id integer,
    month integer,
    group_id integer,
    goal_num integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom2_goal_group OWNER TO yournet;

--
-- Name: telecom2_group; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom2_group (
    group_id integer,
    group_name text,
    shop_id integer,
    month integer,
    leader_employee_id integer,
    allot real,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom2_group OWNER TO yournet;

--
-- Name: telecom2_group_const; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom2_group_const (
    group_const_id integer,
    shop_id integer,
    month integer,
    group_id integer,
    employee_id integer,
    add_date timestamp without time zone,
    status character(1),
    short_name text
);


ALTER TABLE public.telecom2_group_const OWNER TO yournet;

--
-- Name: telecom2_group_const_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE telecom2_group_const_id_seq
    START WITH 1001
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.telecom2_group_const_id_seq OWNER TO yournet;

--
-- Name: telecom2_group_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE telecom2_group_id_seq
    START WITH 101
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.telecom2_group_id_seq OWNER TO yournet;

--
-- Name: telecom2_item; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom2_item (
    item_id integer,
    item_name text,
    shop_id integer,
    month integer,
    bigitem_id integer,
    score integer,
    order_num integer,
    add_date timestamp without time zone,
    status character(1),
    noinput character(1)
);


ALTER TABLE public.telecom2_item OWNER TO yournet;

--
-- Name: telecom2_item_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE telecom2_item_id_seq
    START WITH 101
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.telecom2_item_id_seq OWNER TO yournet;

--
-- Name: telecom2_mail_log; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom2_mail_log (
    mail_id integer,
    shop_id integer,
    employee_id integer,
    to_ls text[],
    cc_ls text[],
    bcc_ls text[],
    from1 text,
    title text,
    message text,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom2_mail_log OWNER TO yournet;

--
-- Name: telecom2_mail_log_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE telecom2_mail_log_id_seq
    START WITH 10
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.telecom2_mail_log_id_seq OWNER TO yournet;

--
-- Name: telecom2_shop; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom2_shop (
    shop_id integer,
    shop_name text,
    manager_employee_id integer,
    kind integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom2_shop OWNER TO yournet;

--
-- Name: telecom2_unitname; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom2_unitname (
    shop_id integer,
    month integer,
    item_id integer,
    u_name text,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom2_unitname OWNER TO yournet;

--
-- Name: telecom_action; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom_action (
    shop_id integer,
    item_id integer,
    month integer,
    day integer,
    employee_id integer,
    dgoal_num integer,
    action_num integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom_action OWNER TO yournet;

--
-- Name: telecom_bigitem; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom_bigitem (
    bigitem_id integer,
    bigitem_name text,
    shop_id integer,
    month integer,
    order_num integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom_bigitem OWNER TO yournet;

--
-- Name: telecom_bigitem_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE telecom_bigitem_id_seq
    START WITH 11
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.telecom_bigitem_id_seq OWNER TO yournet;

--
-- Name: telecom_comment; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom_comment (
    comment_id integer,
    shop_id integer,
    employee_id integer,
    comment text,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom_comment OWNER TO yournet;

--
-- Name: telecom_comment_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE telecom_comment_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.telecom_comment_id_seq OWNER TO yournet;

--
-- Name: telecom_goal; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom_goal (
    shop_id integer,
    item_id integer,
    month integer,
    employee_id integer,
    goal_num integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom_goal OWNER TO yournet;

--
-- Name: telecom_goal_day; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom_goal_day (
    shop_id integer,
    item_id integer,
    month integer,
    day integer,
    dgoal_num integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom_goal_day OWNER TO yournet;

--
-- Name: telecom_goal_group; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom_goal_group (
    shop_id integer,
    item_id integer,
    month integer,
    group_id integer,
    goal_num integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom_goal_group OWNER TO yournet;

--
-- Name: telecom_group; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom_group (
    group_id integer,
    group_name text,
    shop_id integer,
    month integer,
    leader_employee_id integer,
    allot real,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom_group OWNER TO yournet;

--
-- Name: telecom_group_const; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom_group_const (
    group_const_id integer,
    shop_id integer,
    month integer,
    group_id integer,
    employee_id integer,
    add_date timestamp without time zone,
    status character(1),
    short_name text
);


ALTER TABLE public.telecom_group_const OWNER TO yournet;

--
-- Name: telecom_group_const_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE telecom_group_const_id_seq
    START WITH 1001
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.telecom_group_const_id_seq OWNER TO yournet;

--
-- Name: telecom_group_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE telecom_group_id_seq
    START WITH 101
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.telecom_group_id_seq OWNER TO yournet;

--
-- Name: telecom_item; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom_item (
    item_id integer,
    item_name text,
    shop_id integer,
    month integer,
    bigitem_id integer,
    score integer,
    order_num integer,
    add_date timestamp without time zone,
    status character(1),
    noinput character(1)
);


ALTER TABLE public.telecom_item OWNER TO yournet;

--
-- Name: telecom_item_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE telecom_item_id_seq
    START WITH 101
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.telecom_item_id_seq OWNER TO yournet;

--
-- Name: telecom_mail_log; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom_mail_log (
    mail_id integer,
    shop_id integer,
    employee_id integer,
    to_ls text[],
    cc_ls text[],
    bcc_ls text[],
    from1 text,
    title text,
    message text,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom_mail_log OWNER TO yournet;

--
-- Name: telecom_mail_log_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE telecom_mail_log_id_seq
    START WITH 10
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.telecom_mail_log_id_seq OWNER TO yournet;

--
-- Name: telecom_shop; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom_shop (
    shop_id integer,
    shop_name text,
    manager_employee_id integer,
    kind integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom_shop OWNER TO yournet;

--
-- Name: telecom_unitname; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE telecom_unitname (
    shop_id integer,
    month integer,
    item_id integer,
    u_name text,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.telecom_unitname OWNER TO yournet;

--
-- Name: top_dashboard; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE top_dashboard (
    employee_id integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.top_dashboard OWNER TO yournet;

SET default_with_oids = false;

--
-- Name: yudai_data_news; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE yudai_data_news (
    date date,
    shop_id text,
    budget bigint,
    revenue bigint,
    customers_num integer,
    work_time double precision,
    discount_ticket bigint
);


ALTER TABLE public.yudai_data_news OWNER TO yournet;

--
-- Name: id; Type: DEFAULT; Schema: public; Owner: yournet
--

ALTER TABLE ONLY date_weather ALTER COLUMN id SET DEFAULT nextval('date_weather_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: yournet
--

ALTER TABLE ONLY infomart_inventory ALTER COLUMN id SET DEFAULT nextval('infomart_inventory_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: yournet
--

ALTER TABLE ONLY kpi_managements ALTER COLUMN id SET DEFAULT nextval('kpi_managements_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: yournet
--

ALTER TABLE ONLY man_hour ALTER COLUMN id SET DEFAULT nextval('man_hour_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: yournet
--

ALTER TABLE ONLY onsen_budget ALTER COLUMN id SET DEFAULT nextval('onsen_budget_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: yournet
--

ALTER TABLE ONLY onsen_daily_work_report ALTER COLUMN id SET DEFAULT nextval('onsen_daily_work_report_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: yournet
--

ALTER TABLE ONLY onsen_report_category ALTER COLUMN id SET DEFAULT nextval('onsen_report_category_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: yournet
--

ALTER TABLE ONLY shop_budget_month ALTER COLUMN id SET DEFAULT nextval('shop_budget_month_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: yournet
--

ALTER TABLE ONLY standard_unit_price ALTER COLUMN id SET DEFAULT nextval('standard_unit_price_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: yournet
--

ALTER TABLE ONLY store_survey ALTER COLUMN id SET DEFAULT nextval('store_survey_id_seq'::regclass);


--
-- Name: dashboard_dashboard_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY dashboard
    ADD CONSTRAINT dashboard_dashboard_id_key UNIQUE (dashboard_id);


--
-- Name: date_weather_pkey; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY date_weather
    ADD CONSTRAINT date_weather_pkey PRIMARY KEY (id);


--
-- Name: expense_list_pkey; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY expense_list
    ADD CONSTRAINT expense_list_pkey PRIMARY KEY (code);


--
-- Name: infomart_inventory_pkey; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY infomart_inventory
    ADD CONSTRAINT infomart_inventory_pkey PRIMARY KEY (id);


--
-- Name: kpi_managements_pkey; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY kpi_managements
    ADD CONSTRAINT kpi_managements_pkey PRIMARY KEY (id);


--
-- Name: magnet_ptcard_log_ptlog_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY magnet_ptcard_log
    ADD CONSTRAINT magnet_ptcard_log_ptlog_id_key UNIQUE (ptlog_id);


--
-- Name: man_hour_pkey; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY man_hour
    ADD CONSTRAINT man_hour_pkey PRIMARY KEY (id);


--
-- Name: manage_money_check_manage_money_check_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY manage_money_check
    ADD CONSTRAINT manage_money_check_manage_money_check_id_key UNIQUE (manage_money_check_id);


--
-- Name: onsen_budget_pkey; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY onsen_budget
    ADD CONSTRAINT onsen_budget_pkey PRIMARY KEY (id);


--
-- Name: onsen_daily_work_report_pkey; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY onsen_daily_work_report
    ADD CONSTRAINT onsen_daily_work_report_pkey PRIMARY KEY (id);


--
-- Name: onsen_report_category_pkey; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY onsen_report_category
    ADD CONSTRAINT onsen_report_category_pkey PRIMARY KEY (id);


--
-- Name: pos_titem_pos_titem_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_titem
    ADD CONSTRAINT pos_titem_pos_titem_id_key UNIQUE (pos_titem_id);


--
-- Name: pos_tslip_pos_tslip_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_tslip
    ADD CONSTRAINT pos_tslip_pos_tslip_id_key UNIQUE (pos_tslip_id);


--
-- Name: pos_ttend_pos_ttend_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_ttend
    ADD CONSTRAINT pos_ttend_pos_ttend_id_key UNIQUE (pos_ttend_id);


--
-- Name: pos_ttotal_pos_ttotal_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_ttotal
    ADD CONSTRAINT pos_ttotal_pos_ttotal_id_key UNIQUE (pos_ttotal_id);


--
-- Name: pos_zaudit_pos_zaudit_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_zaudit
    ADD CONSTRAINT pos_zaudit_pos_zaudit_id_key UNIQUE (pos_zaudit_id);


--
-- Name: pos_zdealer_pos_zdealer_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_zdealer
    ADD CONSTRAINT pos_zdealer_pos_zdealer_id_key UNIQUE (pos_zdealer_id);


--
-- Name: pos_zfloor_pos_zfloor_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_zfloor
    ADD CONSTRAINT pos_zfloor_pos_zfloor_id_key UNIQUE (pos_zfloor_id);


--
-- Name: pos_zfree_pos_zfree_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_zfree
    ADD CONSTRAINT pos_zfree_pos_zfree_id_key UNIQUE (pos_zfree_id);


--
-- Name: pos_zfuse_pos_zfuse_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_zfuse
    ADD CONSTRAINT pos_zfuse_pos_zfuse_id_key UNIQUE (pos_zfuse_id);


--
-- Name: pos_zitem_pos_zitem_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_zitem
    ADD CONSTRAINT pos_zitem_pos_zitem_id_key UNIQUE (pos_zitem_id);


--
-- Name: pos_zkkak_pos_zkkak_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_zkkak
    ADD CONSTRAINT pos_zkkak_pos_zkkak_id_key UNIQUE (pos_zkkak_id);


--
-- Name: pos_zkkum_pos_zkkum_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_zkkum
    ADD CONSTRAINT pos_zkkum_pos_zkkum_id_key UNIQUE (pos_zkkum_id);


--
-- Name: pos_ztair_pos_ztair_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_ztair
    ADD CONSTRAINT pos_ztair_pos_ztair_id_key UNIQUE (pos_ztair_id);


--
-- Name: pos_zticket_pos_zticket_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_zticket
    ADD CONSTRAINT pos_zticket_pos_zticket_id_key UNIQUE (pos_zticket_id);


--
-- Name: pos_ztime_pos_ztime_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_ztime
    ADD CONSTRAINT pos_ztime_pos_ztime_id_key UNIQUE (pos_ztime_id);


--
-- Name: pos_ztotal_discount_pos_ztotal_discount_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_ztotal_discount
    ADD CONSTRAINT pos_ztotal_discount_pos_ztotal_discount_id_key UNIQUE (pos_ztotal_discount_id);


--
-- Name: pos_ztotal_note_pos_ztotal_note_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_ztotal_note
    ADD CONSTRAINT pos_ztotal_note_pos_ztotal_note_id_key UNIQUE (pos_ztotal_note_id);


--
-- Name: pos_ztotal_pay_pos_ztotal_pay_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_ztotal_pay
    ADD CONSTRAINT pos_ztotal_pay_pos_ztotal_pay_id_key UNIQUE (pos_ztotal_pay_id);


--
-- Name: pos_ztotal_sales_pos_ztotal_sales_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY pos_ztotal_sales
    ADD CONSTRAINT pos_ztotal_sales_pos_ztotal_sales_id_key UNIQUE (pos_ztotal_sales_id);


--
-- Name: session_session_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY session
    ADD CONSTRAINT session_session_id_key UNIQUE (session_id);


--
-- Name: shop_budget_month_pkey; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY shop_budget_month
    ADD CONSTRAINT shop_budget_month_pkey PRIMARY KEY (id);


--
-- Name: shop_list_pkey; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY shop_list
    ADD CONSTRAINT shop_list_pkey PRIMARY KEY (id);


--
-- Name: standard_unit_price_pkey; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY standard_unit_price
    ADD CONSTRAINT standard_unit_price_pkey PRIMARY KEY (id);


--
-- Name: store_survey_pkey; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY store_survey
    ADD CONSTRAINT store_survey_pkey PRIMARY KEY (id);


--
-- Name: top_dashboard_employee_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY top_dashboard
    ADD CONSTRAINT top_dashboard_employee_id_key UNIQUE (employee_id);


--
-- Name: yudai_data_unique_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY yudai_data_news
    ADD CONSTRAINT yudai_data_unique_key UNIQUE (date, shop_id);


--
-- Name: chatbox_chat_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX chatbox_chat_id_idx ON chatbox USING btree (chat_id);


--
-- Name: ck_category_list_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE UNIQUE INDEX ck_category_list_id_idx ON ck_category_list USING btree (category_id);


--
-- Name: ck_check_action_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE UNIQUE INDEX ck_check_action_id_idx ON ck_check_action USING btree (ckaction_id);


--
-- Name: ck_check_action_list_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE UNIQUE INDEX ck_check_action_list_id_idx ON ck_check_action_list USING btree (ckaction_list_id);


--
-- Name: ck_check_set_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE UNIQUE INDEX ck_check_set_id_idx ON ck_check_set USING btree (ckset_id);


--
-- Name: ck_item_list_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE UNIQUE INDEX ck_item_list_id_idx ON ck_item_list USING btree (item_id);


--
-- Name: ck_reply_ckaction_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX ck_reply_ckaction_id_idx ON ck_reply USING btree (ckaction_id);


--
-- Name: ck_reply_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE UNIQUE INDEX ck_reply_id_idx ON ck_reply USING btree (reply_id);


--
-- Name: ck_viewlog_ckaction_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX ck_viewlog_ckaction_id_idx ON ck_viewlog USING btree (ckaction_id);


--
-- Name: common_info_category_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX common_info_category_idx ON common_info USING btree (category_id);


--
-- Name: common_info_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX common_info_id_idx ON common_info USING btree (common_id);


--
-- Name: common_info_tabs_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX common_info_tabs_idx ON common_info USING btree (tabs);


--
-- Name: consult_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX consult_id_idx ON consult USING btree (consult_id);


--
-- Name: dashboard_employee_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX dashboard_employee_idx ON dashboard USING btree (employee_id);


--
-- Name: docu_manage_file_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX docu_manage_file_id_idx ON docu_manage USING btree (file_id);


--
-- Name: employee_list_tmp_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE UNIQUE INDEX employee_list_tmp_id_idx ON employee_list_tmp USING btree (employee_id_tmp);


--
-- Name: employee_list_tmp_num_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE UNIQUE INDEX employee_list_tmp_num_idx ON employee_list_tmp USING btree (employee_num);


--
-- Name: lunch_dinner_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX lunch_dinner_sale_date_idx ON lunch_dinner USING btree (sale_date);


--
-- Name: lunch_dinner_shop_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX lunch_dinner_shop_idx ON lunch_dinner USING btree (shop_id);


--
-- Name: lunch_time_shop_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX lunch_time_shop_idx ON lunch_time USING btree (shop_id);


--
-- Name: magnet_ptcard_log_record_time_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX magnet_ptcard_log_record_time_idx ON magnet_ptcard_log USING btree (record_time);


--
-- Name: magnet_ptcard_log_shop_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX magnet_ptcard_log_shop_idx ON magnet_ptcard_log USING btree (shop_id);


--
-- Name: manage_money_check_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX manage_money_check_pos_shopno_idx ON manage_money_check USING btree (pos_shopno);


--
-- Name: manage_money_check_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX manage_money_check_sale_date_idx ON manage_money_check USING btree (sale_date);


--
-- Name: manager_pdca_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX manager_pdca_id_idx ON manager_pdca USING btree (pdca_id);


--
-- Name: message_log_employee_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX message_log_employee_id_idx ON message_log USING btree (employee_id);


--
-- Name: message_log_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX message_log_id_idx ON message_log USING btree (message_id);


--
-- Name: message_message_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX message_message_id_idx ON message USING btree (message_id);


--
-- Name: order_main_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX order_main_id_idx ON order_main USING btree (order_id);


--
-- Name: order_main_log_order_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX order_main_log_order_id_idx ON order_main_log USING btree (order_id);


--
-- Name: order_shooting_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX order_shooting_id_idx ON order_shooting USING btree (shooting_id);


--
-- Name: order_tool_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX order_tool_id_idx ON order_tool USING btree (tool_id);


--
-- Name: pos_intable_check_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_intable_check_idx ON pos_intable_check USING btree (pos_shopno, sale_date);


--
-- Name: pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_shopno_idx ON pos_ztotal_sales USING btree (pos_shopno);


--
-- Name: pos_titem_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_titem_pos_shopno_idx ON pos_titem USING btree (pos_shopno);


--
-- Name: pos_titem_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_titem_sale_date_idx ON pos_titem USING btree (sale_date);


--
-- Name: pos_tslip_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_tslip_pos_shopno_idx ON pos_tslip USING btree (pos_shopno);


--
-- Name: pos_tslip_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_tslip_sale_date_idx ON pos_tslip USING btree (sale_date);


--
-- Name: pos_ttend_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_ttend_pos_shopno_idx ON pos_ttend USING btree (pos_shopno);


--
-- Name: pos_ttend_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_ttend_sale_date_idx ON pos_ttend USING btree (sale_date);


--
-- Name: pos_ttotal_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_ttotal_pos_shopno_idx ON pos_ttotal USING btree (pos_shopno);


--
-- Name: pos_ttotal_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_ttotal_sale_date_idx ON pos_ttotal USING btree (sale_date);


--
-- Name: pos_zaudit_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_zaudit_pos_shopno_idx ON pos_zaudit USING btree (pos_shopno);


--
-- Name: pos_zaudit_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_zaudit_sale_date_idx ON pos_zaudit USING btree (sale_date);


--
-- Name: pos_zdealer_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_zdealer_pos_shopno_idx ON pos_zdealer USING btree (pos_shopno);


--
-- Name: pos_zdealer_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_zdealer_sale_date_idx ON pos_zdealer USING btree (sale_date);


--
-- Name: pos_zfloor_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_zfloor_pos_shopno_idx ON pos_zfloor USING btree (pos_shopno);


--
-- Name: pos_zfloor_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_zfloor_sale_date_idx ON pos_zfloor USING btree (sale_date);


--
-- Name: pos_zfree_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_zfree_pos_shopno_idx ON pos_zfree USING btree (pos_shopno);


--
-- Name: pos_zfree_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_zfree_sale_date_idx ON pos_zfree USING btree (sale_date);


--
-- Name: pos_zfuse_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_zfuse_pos_shopno_idx ON pos_zfuse USING btree (pos_shopno);


--
-- Name: pos_zfuse_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_zfuse_sale_date_idx ON pos_zfuse USING btree (sale_date);


--
-- Name: pos_zitem_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_zitem_pos_shopno_idx ON pos_zitem USING btree (pos_shopno);


--
-- Name: pos_zitem_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_zitem_sale_date_idx ON pos_zitem USING btree (sale_date);


--
-- Name: pos_zkkak_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_zkkak_pos_shopno_idx ON pos_zkkak USING btree (pos_shopno);


--
-- Name: pos_zkkak_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_zkkak_sale_date_idx ON pos_zkkak USING btree (sale_date);


--
-- Name: pos_zkkum_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_zkkum_pos_shopno_idx ON pos_zkkum USING btree (pos_shopno);


--
-- Name: pos_zkkum_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_zkkum_sale_date_idx ON pos_zkkum USING btree (sale_date);


--
-- Name: pos_ztair_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_ztair_pos_shopno_idx ON pos_ztair USING btree (pos_shopno);


--
-- Name: pos_ztair_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_ztair_sale_date_idx ON pos_ztair USING btree (sale_date);


--
-- Name: pos_zticket_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_zticket_pos_shopno_idx ON pos_zticket USING btree (pos_shopno);


--
-- Name: pos_zticket_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_zticket_sale_date_idx ON pos_zticket USING btree (sale_date);


--
-- Name: pos_ztime_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_ztime_pos_shopno_idx ON pos_ztime USING btree (pos_shopno);


--
-- Name: pos_ztime_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_ztime_sale_date_idx ON pos_ztime USING btree (sale_date);


--
-- Name: pos_ztotal_discount_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_ztotal_discount_pos_shopno_idx ON pos_ztotal_discount USING btree (pos_shopno);


--
-- Name: pos_ztotal_discount_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_ztotal_discount_sale_date_idx ON pos_ztotal_discount USING btree (sale_date);


--
-- Name: pos_ztotal_note_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_ztotal_note_pos_shopno_idx ON pos_ztotal_note USING btree (pos_shopno);


--
-- Name: pos_ztotal_note_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_ztotal_note_sale_date_idx ON pos_ztotal_note USING btree (sale_date);


--
-- Name: pos_ztotal_pay_pos_shopno_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_ztotal_pay_pos_shopno_idx ON pos_ztotal_pay USING btree (pos_shopno);


--
-- Name: pos_ztotal_pay_sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX pos_ztotal_pay_sale_date_idx ON pos_ztotal_pay USING btree (sale_date);


--
-- Name: sale_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX sale_date_idx ON pos_ztotal_sales USING btree (sale_date);


--
-- Name: session_employee_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX session_employee_idx ON session USING btree (employee_id);


--
-- Name: shop_performance_month_unique; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE UNIQUE INDEX shop_performance_month_unique ON shop_performance_month USING btree (shop_id, month, expense_code);


--
-- Name: telecom2_action_month_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX telecom2_action_month_idx ON telecom2_action USING btree (month);


--
-- Name: telecom2_bigitem_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX telecom2_bigitem_id_idx ON telecom2_bigitem USING btree (bigitem_id);


--
-- Name: telecom2_goal_day_month_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX telecom2_goal_day_month_idx ON telecom2_goal_day USING btree (month);


--
-- Name: telecom2_goal_group_month_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX telecom2_goal_group_month_idx ON telecom2_goal USING btree (month);


--
-- Name: telecom2_goal_month_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX telecom2_goal_month_idx ON telecom2_goal USING btree (month);


--
-- Name: telecom2_group_const_month_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX telecom2_group_const_month_idx ON telecom2_group_const USING btree (month);


--
-- Name: telecom2_group_month_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX telecom2_group_month_idx ON telecom2_group USING btree (month);


--
-- Name: telecom2_item_month_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX telecom2_item_month_idx ON telecom2_item USING btree (month);


--
-- Name: telecom2_shop_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX telecom2_shop_id_idx ON telecom2_shop USING btree (shop_id);


--
-- Name: telecom_action_month_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX telecom_action_month_idx ON telecom_action USING btree (month);


--
-- Name: telecom_bigitem_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX telecom_bigitem_id_idx ON telecom_bigitem USING btree (bigitem_id);


--
-- Name: telecom_goal_day_month_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX telecom_goal_day_month_idx ON telecom_goal_day USING btree (month);


--
-- Name: telecom_goal_group_month_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX telecom_goal_group_month_idx ON telecom_goal USING btree (month);


--
-- Name: telecom_goal_month_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX telecom_goal_month_idx ON telecom_goal USING btree (month);


--
-- Name: telecom_group_const_month_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX telecom_group_const_month_idx ON telecom_group_const USING btree (month);


--
-- Name: telecom_group_month_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX telecom_group_month_idx ON telecom_group USING btree (month);


--
-- Name: telecom_item_month_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX telecom_item_month_idx ON telecom_item USING btree (month);


--
-- Name: telecom_shop_id_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX telecom_shop_id_idx ON telecom_shop USING btree (shop_id);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--
