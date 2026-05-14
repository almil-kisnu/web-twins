-- File: supabase_performa_toko_rfc.sql

-- 1. Yearly Store Performance Summary (Across All Active Stores)
CREATE OR REPLACE FUNCTION get_store_performance_yearly(p_year INT)
RETURNS TABLE (
    store_id UUID,
    nama_toko TEXT,
    bulan INT,
    omset NUMERIC,
    hpp NUMERIC,
    pemasukan NUMERIC,
    pengeluaran NUMERIC,
    rugi NUMERIC
) AS $$
BEGIN
  RETURN QUERY
    SELECT 
        s.uuid AS store_id,
        s.nama::TEXT AS nama_toko,
        m.bulan::INT,
        
        -- Omset (Penjualan)
        COALESCE((
            (SELECT COALESCE(SUM(t.total), 0) 
            FROM transactions t 
            WHERE t.store_id = s.uuid AND EXTRACT(MONTH FROM t.tanggal) = m.bulan AND EXTRACT(YEAR FROM t.tanggal) = p_year AND t.jenis = 'penjualan')
            +
            (SELECT COALESCE(SUM(po.total_amount), 0)
            FROM payment_orders po
            WHERE po.outlet_id = s.uuid::TEXT AND EXTRACT(MONTH FROM po.created_at) = m.bulan AND EXTRACT(YEAR FROM po.created_at) = p_year AND po.payment_status IN ('paid', 'settlement', 'success', 'capture'))
        ), 0)::NUMERIC AS omset,
        
        -- HPP
        COALESCE((
            (SELECT COALESCE(SUM(td.harga_modal * td.jmlh), 0)
            FROM transaction_detail td
            INNER JOIN transactions t ON t.uuid = td.transaction_id
            WHERE t.store_id = s.uuid AND EXTRACT(MONTH FROM t.tanggal) = m.bulan AND EXTRACT(YEAR FROM t.tanggal) = p_year AND t.jenis = 'penjualan')
            +
            (SELECT COALESCE(SUM(p.harga_modal * poi.quantity), 0)
            FROM payment_order_items poi
            INNER JOIN payment_orders po ON po.id = poi.payment_order_id
            LEFT JOIN products p ON p.uuid::TEXT = poi.product_id
            WHERE po.outlet_id = s.uuid::TEXT AND EXTRACT(MONTH FROM po.created_at) = m.bulan AND EXTRACT(YEAR FROM po.created_at) = p_year AND po.payment_status IN ('paid', 'settlement', 'success', 'capture'))
        ), 0)::NUMERIC AS hpp,
        
        -- Pemasukan = cash_flows pemasukan + pelunasan piutang
        COALESCE((
            (SELECT COALESCE(SUM(nominal), 0) FROM cash_flows cf WHERE cf.store_id = s.uuid AND EXTRACT(MONTH FROM cf.tanggal) = m.bulan AND EXTRACT(YEAR FROM cf.tanggal) = p_year AND cf.jenis = 'pemasukan') +
            (SELECT COALESCE(SUM(dd.bayar), 0) FROM detail_debts dd INNER JOIN debts d ON d.uuid = dd.debts_id WHERE d.tipe = 'piutang' AND d.store_id = s.uuid AND EXTRACT(MONTH FROM dd.tanggal) = m.bulan AND EXTRACT(YEAR FROM dd.tanggal) = p_year)
        ), 0)::NUMERIC AS pemasukan,
        
        -- Pengeluaran = cash_flows pengeluaran + pembayaran utang
        COALESCE((
            (SELECT COALESCE(SUM(nominal), 0) FROM cash_flows cf WHERE cf.store_id = s.uuid AND EXTRACT(MONTH FROM cf.tanggal) = m.bulan AND EXTRACT(YEAR FROM cf.tanggal) = p_year AND cf.jenis = 'pengeluaran') +
            (SELECT COALESCE(SUM(dd.bayar), 0) FROM detail_debts dd INNER JOIN debts d ON d.uuid = dd.debts_id WHERE d.tipe = 'utang' AND d.store_id = s.uuid AND EXTRACT(MONTH FROM dd.tanggal) = m.bulan AND EXTRACT(YEAR FROM dd.tanggal) = p_year)
        ), 0)::NUMERIC AS pengeluaran,
        
        -- Rugi
        COALESCE((
            SELECT SUM(t.total)
            FROM transactions t
            WHERE t.store_id = s.uuid AND EXTRACT(MONTH FROM t.tanggal) = m.bulan AND EXTRACT(YEAR FROM t.tanggal) = p_year AND t.jenis = 'rugi'
        ), 0)::NUMERIC AS rugi

    FROM store s
    CROSS JOIN (SELECT generate_series(1, 12) AS bulan) m
    WHERE s.status_aktif = true
    ORDER BY s.nama, m.bulan;
END;
$$ LANGUAGE plpgsql;
