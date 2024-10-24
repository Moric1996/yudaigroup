--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'EUC_JP';
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
-- Name: btree_gin; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS btree_gin WITH SCHEMA public;


--
-- Name: EXTENSION btree_gin; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION btree_gin IS 'support for indexing common datatypes in GIN';


--
-- Name: btree_gist; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS btree_gist WITH SCHEMA public;


--
-- Name: EXTENSION btree_gist; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION btree_gist IS 'support for indexing common datatypes in GiST';


--
-- Name: hstore; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS hstore WITH SCHEMA public;


--
-- Name: EXTENSION hstore; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION hstore IS 'data type for storing sets of (key, value) pairs';


--
-- Name: pgcrypto; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;


--
-- Name: EXTENSION pgcrypto; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION pgcrypto IS 'cryptographic functions';


SET search_path = public, pg_catalog;

--
-- Name: member_password(); Type: FUNCTION; Schema: public; Owner: nexsite
--

CREATE FUNCTION member_password() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
IF TG_OP = 'INSERT' OR (TG_OP = 'UPDATE' AND (OLD.password <> NEW.password OR OLD.password IS NULL)) THEN
NEW.password = crypt(NEW.password ,gen_salt('bf'));
RAISE NOTICE 'encryption password is %',NEW.password;
END IF;
RETURN NEW;
END;
$$;


ALTER FUNCTION public.member_password() OWNER TO nexsite;

SET default_tablespace = '';

SET default_with_oids = true;

--
-- Name: asset; Type: TABLE; Schema: public; Owner: nexsite; Tablespace: 
--

CREATE TABLE asset (
    id integer NOT NULL,
    code integer,
    name text,
    status integer DEFAULT 1 NOT NULL,
    category integer,
    count integer,
    price integer,
    emp_id text,
    add_date date,
    comment text
);


ALTER TABLE public.asset OWNER TO nexsite;

--
-- Name: asset_id_seq; Type: SEQUENCE; Schema: public; Owner: nexsite
--

CREATE SEQUENCE asset_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.asset_id_seq OWNER TO nexsite;

--
-- Name: asset_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: nexsite
--

ALTER SEQUENCE asset_id_seq OWNED BY asset.id;


--
-- Name: asset_rent; Type: TABLE; Schema: public; Owner: nexsite; Tablespace: 
--

CREATE TABLE asset_rent (
    id integer NOT NULL,
    asset_id text NOT NULL,
    status integer DEFAULT 1 NOT NULL,
    member text,
    reason integer,
    place integer,
    start_date timestamp without time zone,
    end_date timestamp without time zone,
    add_date timestamp without time zone DEFAULT '2022-03-03 15:42:45.936698'::timestamp without time zone NOT NULL,
    comment text
);


ALTER TABLE public.asset_rent OWNER TO nexsite;

--
-- Name: asset_rent_id_seq; Type: SEQUENCE; Schema: public; Owner: nexsite
--

CREATE SEQUENCE asset_rent_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.asset_rent_id_seq OWNER TO nexsite;

--
-- Name: asset_rent_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: nexsite
--

ALTER SEQUENCE asset_rent_id_seq OWNED BY asset_rent.id;


--
-- Name: asset_specification; Type: TABLE; Schema: public; Owner: nexsite; Tablespace: 
--

CREATE TABLE asset_specification (
    id integer NOT NULL,
    code integer,
    asset_id text NOT NULL,
    name text,
    status integer,
    maker text,
    category integer,
    serial_no text,
    os text,
    warranty_date date,
    warranty text,
    add_date timestamp without time zone DEFAULT '2022-03-03 15:41:38.539476'::timestamp without time zone NOT NULL,
    comment text
);


ALTER TABLE public.asset_specification OWNER TO nexsite;

--
-- Name: asset_specification_id_seq; Type: SEQUENCE; Schema: public; Owner: nexsite
--

CREATE SEQUENCE asset_specification_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.asset_specification_id_seq OWNER TO nexsite;

--
-- Name: asset_specification_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: nexsite
--

ALTER SEQUENCE asset_specification_id_seq OWNED BY asset_specification.id;


--
-- Name: auth_list; Type: TABLE; Schema: public; Owner: nexsite; Tablespace: 
--

CREATE TABLE auth_list (
    auth_list_id integer,
    name text,
    add_date timestamp without time zone
);


ALTER TABLE public.auth_list OWNER TO nexsite;

--
-- Name: auth_list_seq; Type: SEQUENCE; Schema: public; Owner: nexsite
--

CREATE SEQUENCE auth_list_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.auth_list_seq OWNER TO nexsite;

--
-- Name: auth_mem; Type: TABLE; Schema: public; Owner: nexsite; Tablespace: 
--

CREATE TABLE auth_mem (
    auth_mem_id integer,
    auth_list_id integer,
    mem_id text,
    level integer,
    add_date timestamp without time zone
);


ALTER TABLE public.auth_mem OWNER TO nexsite;

--
-- Name: auth_mem_seq; Type: SEQUENCE; Schema: public; Owner: nexsite
--

CREATE SEQUENCE auth_mem_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.auth_mem_seq OWNER TO nexsite;

--
-- Name: bumon; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE bumon (
    bumon_code text,
    bumon_name text
);


ALTER TABLE public.bumon OWNER TO yournet;

SET default_with_oids = false;

--
-- Name: change_log; Type: TABLE; Schema: public; Owner: nexsite; Tablespace: 
--

CREATE TABLE change_log (
    change_log_id integer,
    cluster_id integer,
    mem_id text,
    add_date timestamp without time zone,
    script_name text,
    mode character(1),
    comment text
);


ALTER TABLE public.change_log OWNER TO nexsite;

--
-- Name: change_log_seq; Type: SEQUENCE; Schema: public; Owner: nexsite
--

CREATE SEQUENCE change_log_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.change_log_seq OWNER TO nexsite;

--
-- Name: cluster_log; Type: TABLE; Schema: public; Owner: nexsite; Tablespace: 
--

CREATE TABLE cluster_log (
    cluster_id integer NOT NULL,
    mem_id text,
    access_date timestamp without time zone,
    cluster_data text
);


ALTER TABLE public.cluster_log OWNER TO nexsite;

--
-- Name: cluster_log2; Type: TABLE; Schema: public; Owner: nexsite; Tablespace: 
--

CREATE TABLE cluster_log2 (
    cluster_id integer,
    mem_id text,
    access_date timestamp without time zone,
    cluster_data text
);


ALTER TABLE public.cluster_log2 OWNER TO nexsite;

--
-- Name: cluster_log_seq; Type: SEQUENCE; Schema: public; Owner: nexsite
--

CREATE SEQUENCE cluster_log_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.cluster_log_seq OWNER TO nexsite;

SET default_with_oids = true;

--
-- Name: cronlog; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE cronlog (
    cron_log_id integer,
    server_id integer,
    g_ip text,
    l_ip text,
    username text,
    comment text,
    add_date timestamp without time zone
);


ALTER TABLE public.cronlog OWNER TO yournet;

--
-- Name: cronlog_seq; Type: SEQUENCE; Schema: public; Owner: yournet
--

CREATE SEQUENCE cronlog_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.cronlog_seq OWNER TO yournet;

--
-- Name: fx4_data; Type: TABLE; Schema: public; Owner: nexsite; Tablespace: 
--

CREATE TABLE fx4_data (
    fx4_data_id integer NOT NULL,
    kanri_id integer,
    type_id integer,
    fromat_id integer,
    pre_data text,
    changed_data text,
    add_date timestamp without time zone
);


ALTER TABLE public.fx4_data OWNER TO nexsite;

--
-- Name: fx4_data_id_seq; Type: SEQUENCE; Schema: public; Owner: nexsite
--

CREATE SEQUENCE fx4_data_id_seq
    START WITH 10001
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fx4_data_id_seq OWNER TO nexsite;

--
-- Name: fx4_data_kanri_id_seq; Type: SEQUENCE; Schema: public; Owner: nexsite
--

CREATE SEQUENCE fx4_data_kanri_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fx4_data_kanri_id_seq OWNER TO nexsite;

--
-- Name: fx4_format; Type: TABLE; Schema: public; Owner: nexsite; Tablespace: 
--

CREATE TABLE fx4_format (
    format_id integer NOT NULL,
    type_id integer,
    judge_contents text[],
    tax_class integer,
    debtor_code integer,
    debtor_auxiliary_code text,
    creditor_code integer,
    creditor_auxiliary_code text,
    party text,
    party_code integer,
    abstract text,
    add_col integer,
    add_format text,
    price_col integer,
    abstract_add_col integer,
    keigenzei_col character(1),
    bumon text
);


ALTER TABLE public.fx4_format OWNER TO nexsite;

--
-- Name: fx4_format_id_seq; Type: SEQUENCE; Schema: public; Owner: nexsite
--

CREATE SEQUENCE fx4_format_id_seq
    START WITH 1001
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fx4_format_id_seq OWNER TO nexsite;

--
-- Name: kamoku; Type: TABLE; Schema: public; Owner: nexsite; Tablespace: 
--

CREATE TABLE kamoku (
    kamoku_id integer,
    kamoku_name text,
    tkc_kamoku_name text,
    kamoku_code integer,
    hojo_code text,
    kazei_kubun integer
);


ALTER TABLE public.kamoku OWNER TO nexsite;

--
-- Name: kamoku_seq; Type: SEQUENCE; Schema: public; Owner: nexsite
--

CREATE SEQUENCE kamoku_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.kamoku_seq OWNER TO nexsite;

SET default_with_oids = false;

--
-- Name: koguchi; Type: TABLE; Schema: public; Owner: nexsite; Tablespace: 
--

CREATE TABLE koguchi (
    koguchi_id integer,
    kamoku_id integer,
    mem_id character(10),
    king integer,
    day date,
    add_date timestamp without time zone,
    type character(1),
    aite text,
    memo text,
    bumon_code text,
    supplier_id integer
);


ALTER TABLE public.koguchi OWNER TO nexsite;

--
-- Name: koguchi_seq; Type: SEQUENCE; Schema: public; Owner: nexsite
--

CREATE SEQUENCE koguchi_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.koguchi_seq OWNER TO nexsite;

--
-- Name: member; Type: TABLE; Schema: public; Owner: nexsite; Tablespace: 
--

CREATE TABLE member (
    mem_id character(10),
    pass text,
    name text,
    name_kana text,
    phone text,
    phone_mobile text,
    email text,
    email_mobile text,
    zip_code text,
    address1 text,
    address2 text,
    birth date,
    blood_type text,
    type character(1),
    post text,
    enter_date date,
    status character(1),
    add_date timestamp without time zone,
    retire_date date,
    password text,
    ken_no text,
    kou_no text,
    koyo_no text,
    rosai character(1),
    section_id integer
);


ALTER TABLE public.member OWNER TO nexsite;

SET default_with_oids = true;

--
-- Name: revision; Type: TABLE; Schema: public; Owner: nexsite; Tablespace: 
--

CREATE TABLE revision (
    revision_id integer NOT NULL,
    type_id integer,
    column_no integer,
    word text,
    method integer,
    lists text[]
);


ALTER TABLE public.revision OWNER TO nexsite;

SET default_with_oids = false;

--
-- Name: tbl_test; Type: TABLE; Schema: public; Owner: nexsite; Tablespace: 
--

CREATE TABLE tbl_test (
    id integer,
    pass text
);


ALTER TABLE public.tbl_test OWNER TO nexsite;

SET default_with_oids = true;

--
-- Name: test1; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE test1 (
    id integer,
    income integer,
    income2 integer
);


ALTER TABLE public.test1 OWNER TO yournet;

--
-- Name: test2; Type: TABLE; Schema: public; Owner: yournet; Tablespace: 
--

CREATE TABLE test2 (
    id integer,
    regi integer,
    regi2 integer
);


ALTER TABLE public.test2 OWNER TO yournet;

--
-- Name: type_list; Type: TABLE; Schema: public; Owner: nexsite; Tablespace: 
--

CREATE TABLE type_list (
    type_id integer NOT NULL,
    type_name text,
    judge_column integer[]
);


ALTER TABLE public.type_list OWNER TO nexsite;

--
-- Name: id; Type: DEFAULT; Schema: public; Owner: nexsite
--

ALTER TABLE ONLY asset ALTER COLUMN id SET DEFAULT nextval('asset_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: nexsite
--

ALTER TABLE ONLY asset_rent ALTER COLUMN id SET DEFAULT nextval('asset_rent_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: nexsite
--

ALTER TABLE ONLY asset_specification ALTER COLUMN id SET DEFAULT nextval('asset_specification_id_seq'::regclass);


--
-- Name: cluster_log_pkey; Type: CONSTRAINT; Schema: public; Owner: nexsite; Tablespace: 
--

ALTER TABLE ONLY cluster_log
    ADD CONSTRAINT cluster_log_pkey PRIMARY KEY (cluster_id);


--
-- Name: fx4_data_pkey; Type: CONSTRAINT; Schema: public; Owner: nexsite; Tablespace: 
--

ALTER TABLE ONLY fx4_data
    ADD CONSTRAINT fx4_data_pkey PRIMARY KEY (fx4_data_id);


--
-- Name: fx4_format_pkey; Type: CONSTRAINT; Schema: public; Owner: nexsite; Tablespace: 
--

ALTER TABLE ONLY fx4_format
    ADD CONSTRAINT fx4_format_pkey PRIMARY KEY (format_id);


--
-- Name: revision_pkey; Type: CONSTRAINT; Schema: public; Owner: nexsite; Tablespace: 
--

ALTER TABLE ONLY revision
    ADD CONSTRAINT revision_pkey PRIMARY KEY (revision_id);


--
-- Name: type_list_pkey; Type: CONSTRAINT; Schema: public; Owner: nexsite; Tablespace: 
--

ALTER TABLE ONLY type_list
    ADD CONSTRAINT type_list_pkey PRIMARY KEY (type_id);


--
-- Name: cluster_log_idx1; Type: INDEX; Schema: public; Owner: nexsite; Tablespace: 
--

CREATE INDEX cluster_log_idx1 ON cluster_log USING btree (mem_id);


--
-- Name: member_pkey; Type: INDEX; Schema: public; Owner: nexsite; Tablespace: 
--

CREATE UNIQUE INDEX member_pkey ON member USING btree (mem_id);


--
-- Name: member_password_trigger; Type: TRIGGER; Schema: public; Owner: nexsite
--

CREATE TRIGGER member_password_trigger BEFORE INSERT OR UPDATE ON member FOR EACH ROW EXECUTE PROCEDURE member_password();


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

