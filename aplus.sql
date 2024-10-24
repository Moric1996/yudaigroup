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
-- Name: claim; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE claim (
    claim_id integer,
    claim_date date,
    target_date date,
    company_id integer,
    price_notax integer,
    price_intax integer,
    plan_date date,
    remark text,
    paper_banktrans_id integer,
    paper_banktrans_log_id integer,
    comp_price integer,
    comp_date timestamp without time zone,
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.claim OWNER TO yournet;

--
-- Name: claim_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE claim_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.claim_id_seq OWNER TO yournet;

--
-- Name: company_list; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE company_list (
    company_id integer,
    service_id integer,
    original_id text,
    customer_num text,
    company_name text,
    company_name_kana text,
    email text,
    add_date timestamp without time zone,
    status character(1),
    supplier integer
);


ALTER TABLE public.company_list OWNER TO yournet;

--
-- Name: company_list_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE company_list_id_seq
    START WITH 1001
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.company_list_id_seq OWNER TO yournet;

--
-- Name: paper_banktrans; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE paper_banktrans (
    paper_banktrans_id integer,
    company_id integer,
    bank_code text,
    bank_kana text,
    branch_code text,
    branch_kana text,
    type character(1),
    account_number text,
    account_name text,
    new_flag character(1),
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.paper_banktrans OWNER TO yournet;

--
-- Name: paper_banktrans_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE paper_banktrans_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.paper_banktrans_id_seq OWNER TO yournet;

--
-- Name: paper_banktrans_log; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE paper_banktrans_log (
    paper_banktrans_log_id integer,
    company_id integer,
    trans_date date,
    claim_id integer,
    bank_code text,
    bank_kana text,
    branch_code text,
    branch_kana text,
    type character(1),
    account_number text,
    account_name text,
    money integer,
    new_flag character(1),
    customer_num text,
    trans_result character(1),
    add_date timestamp without time zone,
    status character(1)
);


ALTER TABLE public.paper_banktrans_log OWNER TO yournet;

--
-- Name: paper_banktrans_log_id_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE paper_banktrans_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.paper_banktrans_log_id_seq OWNER TO yournet;

--
-- Name: claim_claim_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY claim
    ADD CONSTRAINT claim_claim_id_key UNIQUE (claim_id);


--
-- Name: company_list_company_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY company_list
    ADD CONSTRAINT company_list_company_id_key UNIQUE (company_id);


--
-- Name: paper_banktrans_log_paper_banktrans_log_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY paper_banktrans_log
    ADD CONSTRAINT paper_banktrans_log_paper_banktrans_log_id_key UNIQUE (paper_banktrans_log_id);


--
-- Name: paper_banktrans_paper_banktrans_id_key; Type: CONSTRAINT; Schema: public; Owner: yournet; Tablespace: 
--

ALTER TABLE ONLY paper_banktrans
    ADD CONSTRAINT paper_banktrans_paper_banktrans_id_key UNIQUE (paper_banktrans_id);


--
-- Name: claim_company_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX claim_company_idx ON claim USING btree (company_id);


--
-- Name: claim_target_date_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX claim_target_date_idx ON claim USING btree (target_date);


--
-- Name: paper_banktrans_company_idx; Type: INDEX; Schema: public; Owner: yournet; Tablespace: 
--

CREATE INDEX paper_banktrans_company_idx ON paper_banktrans USING btree (company_id);


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
