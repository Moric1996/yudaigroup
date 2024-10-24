--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
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


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = true;

--
-- Name: accept_list; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE accept_list (
    accept_list_id integer,
    company_id integer,
    slip_type integer,
    section_id integer,
    accept_type integer,
    accept_name text,
    accept_employees json,
    send_employees json,
    d_jun integer,
    accept_jun integer,
    status character(1)
);


ALTER TABLE public.accept_list OWNER TO yournet;

--
-- Name: accept_list_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE accept_list_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.accept_list_id_seq OWNER TO yournet;

--
-- Name: accept_log; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE accept_log (
    accept_log_id integer,
    slip_id integer,
    accept_list_id integer,
    to_employee_id integer,
    send_flg character(1),
    employee_id integer,
    accept_status integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.accept_log OWNER TO yournet;

--
-- Name: accept_log_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE accept_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.accept_log_id_seq OWNER TO yournet;

--
-- Name: download_log; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE download_log (
    dl_log_id integer,
    slip_id integer,
    attach_no integer,
    employee_id integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.download_log OWNER TO yournet;

--
-- Name: download_log_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE download_log_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.download_log_seq OWNER TO yournet;

--
-- Name: slip; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE slip (
    slip_id integer,
    slip_type integer,
    month date,
    action_date date,
    company_id integer,
    section_id integer,
    money integer,
    supplier integer,
    supplier_other text,
    account integer,
    fee_st integer,
    contents text,
    attach json,
    charge_emp integer,
    last_accept_list_id integer,
    memo text,
    pay_date date,
    add_date timestamp without time zone,
    up_date timestamp without time zone,
    status character(1),
    no_release character(1),
    credit_id integer
);


ALTER TABLE public.slip OWNER TO yournet;

--
-- Name: slip_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE slip_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.slip_id_seq OWNER TO yournet;

--
-- Name: slip_supplier; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE slip_supplier (
    supplier_id integer,
    company_id integer,
    code integer,
    kana text,
    name text,
    count integer,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.slip_supplier OWNER TO yournet;

--
-- Name: supplier_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE supplier_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.supplier_id_seq OWNER TO yournet;

--
-- Name: accept_list_accept_list_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY accept_list
    ADD CONSTRAINT accept_list_accept_list_id_key UNIQUE (accept_list_id);


--
-- Name: accept_log_accept_log_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY accept_log
    ADD CONSTRAINT accept_log_accept_log_id_key UNIQUE (accept_log_id);


--
-- Name: download_log_dl_log_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY download_log
    ADD CONSTRAINT download_log_dl_log_id_key UNIQUE (dl_log_id);


--
-- Name: slip_slip_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY slip
    ADD CONSTRAINT slip_slip_id_key UNIQUE (slip_id);


--
-- Name: slip_supplier_supplier_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY slip_supplier
    ADD CONSTRAINT slip_supplier_supplier_id_key UNIQUE (supplier_id);


--
-- Name: download_log_employee_id_key; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX download_log_employee_id_key ON download_log USING btree (employee_id);


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
