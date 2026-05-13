-- WARNING: This schema is for context only and is not meant to be run.
-- Table order and constraints may not be valid for execution.

CREATE TABLE public.absensi (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  jadwal_id uuid,
  store_id uuid,
  tanggal_absensi date NOT NULL,
  waktu_check_in timestamp without time zone DEFAULT (now() AT TIME ZONE 'Asia/Jakarta'::text),
  status_kehadiran character varying,
  is_recent_sync boolean DEFAULT true,
  CONSTRAINT absensi_pkey PRIMARY KEY (uuid),
  CONSTRAINT absensi_jadwal_id_fkey FOREIGN KEY (jadwal_id) REFERENCES public.jadwal(uuid),
  CONSTRAINT absensi_store_id_fkey FOREIGN KEY (store_id) REFERENCES public.store(uuid)
);
CREATE TABLE public.cash_flows (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  store_id uuid,
  user_id uuid,
  jenis character varying CHECK (jenis::text = ANY (ARRAY['pemasukan'::character varying::text, 'pengeluaran'::character varying::text])),
  nominal numeric DEFAULT 0,
  keterangan text,
  tanggal timestamp without time zone DEFAULT (now() AT TIME ZONE 'Asia/Jakarta'::text),
  metode_pembayaran uuid,
  is_recent_sync boolean DEFAULT true,
  CONSTRAINT cash_flows_pkey PRIMARY KEY (uuid),
  CONSTRAINT cash_flows_metode_pembayaran_fkey FOREIGN KEY (metode_pembayaran) REFERENCES public.payment_methods(uuid),
  CONSTRAINT cash_flows_store_id_fkey FOREIGN KEY (store_id) REFERENCES public.store(uuid),
  CONSTRAINT cash_flows_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(uuid)
);
CREATE TABLE public.category (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  nama_category character varying NOT NULL,
  CONSTRAINT category_pkey PRIMARY KEY (uuid)
);
CREATE TABLE public.contacts (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  store_id uuid,
  nama character varying NOT NULL,
  tipe character varying CHECK (tipe::text = ANY (ARRAY['customer'::character varying, 'supplier'::character varying]::text[])),
  no_hp character varying,
  is_default boolean DEFAULT false,
  CONSTRAINT contacts_pkey PRIMARY KEY (uuid),
  CONSTRAINT contacts_store_id_fkey FOREIGN KEY (store_id) REFERENCES public.store(uuid)
);
CREATE TABLE public.debts (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  store_id uuid,
  kontak_id uuid,
  tipe character varying CHECK (tipe::text = ANY (ARRAY['utang'::character varying, 'piutang'::character varying]::text[])),
  nominal numeric DEFAULT 0,
  sisa numeric DEFAULT 0,
  jatuh_tempo date,
  transaction_id uuid,
  is_recent_sync boolean DEFAULT true,
  CONSTRAINT debts_pkey PRIMARY KEY (uuid),
  CONSTRAINT debts_kontak_id_fkey FOREIGN KEY (kontak_id) REFERENCES public.contacts(uuid),
  CONSTRAINT debts_transaction_id_fkey FOREIGN KEY (transaction_id) REFERENCES public.transactions(uuid),
  CONSTRAINT debts_store_id_fkey FOREIGN KEY (store_id) REFERENCES public.store(uuid)
);
CREATE TABLE public.detail_debts (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  debts_id uuid,
  sebelum numeric DEFAULT 0,
  bayar numeric DEFAULT 0,
  sisa numeric DEFAULT 0,
  metode_pembayaran uuid,
  tanggal timestamp without time zone DEFAULT (now() AT TIME ZONE 'Asia/Jakarta'::text),
  user_id uuid,
  CONSTRAINT detail_debts_pkey PRIMARY KEY (uuid),
  CONSTRAINT detail_debts_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(uuid),
  CONSTRAINT detail_debts_debts_id_fkey FOREIGN KEY (debts_id) REFERENCES public.debts(uuid),
  CONSTRAINT detail_debts_metode_pembayaran_fkey FOREIGN KEY (metode_pembayaran) REFERENCES public.payment_methods(uuid)
);
CREATE TABLE public.fitur (
  nama character varying NOT NULL,
  id integer NOT NULL DEFAULT nextval('fitur_id_seq'::regclass),
  crud boolean DEFAULT false,
  CONSTRAINT fitur_pkey PRIMARY KEY (id)
);
CREATE TABLE public.jadwal (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  user_id uuid,
  shift_id uuid,
  store_id uuid,
  hari_dalam_minggu integer CHECK (hari_dalam_minggu >= 1 AND hari_dalam_minggu <= 7),
  CONSTRAINT jadwal_pkey PRIMARY KEY (uuid),
  CONSTRAINT jadwal_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(uuid),
  CONSTRAINT jadwal_shift_id_fkey FOREIGN KEY (shift_id) REFERENCES public.shifts(uuid),
  CONSTRAINT jadwal_store_id_fkey FOREIGN KEY (store_id) REFERENCES public.store(uuid)
);
CREATE TABLE public.operator (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  nama character varying NOT NULL,
  fitur text,
  pin character varying,
  CONSTRAINT operator_pkey PRIMARY KEY (uuid)
);
CREATE TABLE public.opname (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  tanggal timestamp without time zone DEFAULT (now() AT TIME ZONE 'Asia/Jakarta'::text),
  store_id uuid,
  user_id uuid,
  status character varying DEFAULT 'proses'::character varying CHECK (status::text = ANY (ARRAY['proses'::text, 'selesai'::text])),
  CONSTRAINT opname_pkey PRIMARY KEY (uuid),
  CONSTRAINT opname_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(uuid),
  CONSTRAINT opname_store_id_fkey FOREIGN KEY (store_id) REFERENCES public.store(uuid)
);
CREATE TABLE public.opname_detail (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  opname_id uuid,
  product_id uuid,
  stok_sistem integer DEFAULT 0,
  stok_fisik integer DEFAULT 0,
  selisih integer DEFAULT 0,
  keterangan text,
  CONSTRAINT opname_detail_pkey PRIMARY KEY (uuid),
  CONSTRAINT opname_detail_opname_id_fkey FOREIGN KEY (opname_id) REFERENCES public.opname(uuid),
  CONSTRAINT opname_detail_product_id_fkey FOREIGN KEY (product_id) REFERENCES public.products(uuid)
);
CREATE TABLE public.payment_methods (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  nama_metode character varying NOT NULL,
  CONSTRAINT payment_methods_pkey PRIMARY KEY (uuid)
);
CREATE TABLE public.price_level (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  product_id uuid,
  jmlh integer DEFAULT 0,
  harga numeric DEFAULT 0,
  CONSTRAINT price_level_pkey PRIMARY KEY (uuid),
  CONSTRAINT price_level_product_id_fkey FOREIGN KEY (product_id) REFERENCES public.products(uuid)
);
CREATE TABLE public.product_store (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  product_id uuid,
  store_id uuid,
  stok integer DEFAULT 0,
  kadaluarsa date,
  status_aktif boolean DEFAULT true,
  CONSTRAINT product_store_pkey PRIMARY KEY (uuid),
  CONSTRAINT product_store_product_id_fkey FOREIGN KEY (product_id) REFERENCES public.products(uuid),
  CONSTRAINT product_store_store_id_fkey FOREIGN KEY (store_id) REFERENCES public.store(uuid)
);
CREATE TABLE public.products (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  barcode character varying UNIQUE,
  nama_produk character varying NOT NULL,
  kategori_id uuid,
  harga_modal numeric DEFAULT 0,
  harga_jual numeric DEFAULT 0,
  CONSTRAINT products_pkey PRIMARY KEY (uuid),
  CONSTRAINT products_kategori_id_fkey FOREIGN KEY (kategori_id) REFERENCES public.category(uuid)
);
CREATE TABLE public.shifts (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  nama_shift character varying NOT NULL,
  waktu_mulai time without time zone,
  waktu_selesai time without time zone,
  CONSTRAINT shifts_pkey PRIMARY KEY (uuid)
);
CREATE TABLE public.store (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  nama character varying NOT NULL,
  alamat text,
  notelp character varying,
  status_aktif boolean DEFAULT true,
  bssid character varying,
  CONSTRAINT store_pkey PRIMARY KEY (uuid)
);
CREATE TABLE public.transaction_detail (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  transaction_id uuid,
  product_id uuid,
  jmlh integer DEFAULT 0,
  harga_modal numeric DEFAULT 0,
  harga_jual numeric DEFAULT 0,
  CONSTRAINT transaction_detail_pkey PRIMARY KEY (uuid),
  CONSTRAINT transaction_detail_transaction_id_fkey FOREIGN KEY (transaction_id) REFERENCES public.transactions(uuid),
  CONSTRAINT transaction_detail_product_id_fkey FOREIGN KEY (product_id) REFERENCES public.products(uuid)
);
CREATE TABLE public.transactions (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  diskon numeric DEFAULT 0,
  total numeric DEFAULT 0,
  bayar numeric DEFAULT 0,
  kembalian numeric DEFAULT 0,
  metode_pembayaran uuid,
  tanggal timestamp without time zone DEFAULT (now() AT TIME ZONE 'Asia/Jakarta'::text),
  jenis character varying CHECK (jenis::text = ANY (ARRAY['penjualan'::text, 'pembelian'::text, 'retur'::text, 'rugi'::text, 'transfer'::text])),
  store_id uuid,
  is_recent_sync boolean DEFAULT true,
  pengiriman numeric DEFAULT 0,
  pajak numeric DEFAULT 0,
  user_id uuid,
  contact_id uuid,
  catatan text,
  tujuan_store_id uuid,
  status_transfer character varying DEFAULT 'pending'::character varying CHECK (status_transfer::text = ANY (ARRAY['pending'::character varying, 'diterima'::character varying]::text[])),
  CONSTRAINT transactions_pkey PRIMARY KEY (uuid),
  CONSTRAINT transactions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(uuid),
  CONSTRAINT transactions_metode_pembayaran_fkey FOREIGN KEY (metode_pembayaran) REFERENCES public.payment_methods(uuid),
  CONSTRAINT transactions_tujuan_store_id_fkey FOREIGN KEY (tujuan_store_id) REFERENCES public.store(uuid),
  CONSTRAINT transactions_store_id_fkey FOREIGN KEY (store_id) REFERENCES public.store(uuid),
  CONSTRAINT transactions_contact_id_fkey FOREIGN KEY (contact_id) REFERENCES public.contacts(uuid)
);
CREATE TABLE public.users (
  uuid uuid NOT NULL DEFAULT gen_random_uuid(),
  username character varying NOT NULL UNIQUE,
  password character varying NOT NULL,
  operator_id uuid,
  status_aktif boolean DEFAULT true,
  store_id uuid,
  fcm_token text,
  no_telp character varying,
  CONSTRAINT users_pkey PRIMARY KEY (uuid),
  CONSTRAINT users_operator_id_fkey FOREIGN KEY (operator_id) REFERENCES public.operator(uuid),
  CONSTRAINT users_store_id_fkey FOREIGN KEY (store_id) REFERENCES public.store(uuid)
);