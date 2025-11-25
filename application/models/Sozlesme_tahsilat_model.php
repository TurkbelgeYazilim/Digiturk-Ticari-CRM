<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sözleşme ve Tahsilat Raporları için Merkezi Model
 * 
 * Bu model tüm sözleşme-tahsilat raporları için tek kaynak görevi görür.
 * Sorgu değişiklikleri buradan yapılır, tüm sayfalara otomatik yansır.
 * 
 * @author Batuhan KAHRAMAN
 * @version 1.0.0
 */
class Sozlesme_tahsilat_model extends CI_Model
{
    /**
     * ANA DETAYLI SORGU - Tüm raporların temel kaynağı
     * 
     * Bu sorgu tüm detaylı bilgileri içerir. Diğer raporlar bu sorgudan türetilir.
     * 
     * @param array $filters Filtre parametreleri
     * @return object Query result
     */
    public function get_detayli_rapor($filters = [])
    {
        // WHERE koşulları
        $where = ["c.cari_durum = 1"];
        $params = [];
        
        // Filtreler
        if (!empty($filters['baslangic_tarih']) && !empty($filters['bitis_tarih'])) {
            $where[] = "sf.satis_faturaTarihi BETWEEN ? AND ?";
            $params[] = $filters['baslangic_tarih'];
            $params[] = $filters['bitis_tarih'];
        }
        
        if (!empty($filters['ulke_id'])) {
            $where[] = "il.ulke_id IN (" . implode(',', array_map('intval', $filters['ulke_id'])) . ")";
        }
        
        if (!empty($filters['il_id'])) {
            $where[] = "c.cari_il IN (" . implode(',', array_map('intval', $filters['il_id'])) . ")";
        }
        
        if (!empty($filters['ilce_id'])) {
            $where[] = "c.cari_ilce IN (" . implode(',', array_map('intval', $filters['ilce_id'])) . ")";
        }
        
        if (!empty($filters['bolge_sahibi'])) {
            $where[] = "(yb_ilce.yetki_bolgeleri_bolge_sahibi = ? OR yb_il.yetki_bolgeleri_bolge_sahibi = ?)";
            $params[] = $filters['bolge_sahibi'];
            $params[] = $filters['bolge_sahibi'];
        }
        
        if (!empty($filters['sezon_id'])) {
            $where[] = "(sz_ilce.sezon_id IN (" . implode(',', array_map('intval', $filters['sezon_id'])) . ") OR sz_il.sezon_id IN (" . implode(',', array_map('intval', $filters['sezon_id'])) . "))";
        }
        
        if (!empty($filters['stok_id'])) {
            $where[] = "st.stok_id = ?";
            $params[] = $filters['stok_id'];
        }
        
        if (!empty($filters['personel_id'])) {
            $where[] = "c.cari_olusturan = ?";
            $params[] = $filters['personel_id'];
        }
        
        if (!empty($filters['aktivasyon_hizmet'])) {
            $where[] = "sg.stokGrup_id = ?";
            $params[] = $filters['aktivasyon_hizmet'];
        }
        
        if (!empty($filters['cari_ad'])) {
            $where[] = "c.cari_ad LIKE ?";
            $params[] = '%' . $filters['cari_ad'] . '%';
        }
        
        if (isset($filters['fatura_kesildi']) && $filters['fatura_kesildi'] !== '') {
            $where[] = "sfs.satisStok_faturaKesildi = ?";
            $params[] = $filters['fatura_kesildi'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Ana sorgu
        $sql = "
        SELECT
            c.cari_id        AS 'isletme_no',
            c.cari_ad        AS 'isletme_adi',
            
            -- Ülke adı
            il_ulke.ulke_adi AS 'ulke',
            
            -- Şehir adı
            il.il            AS 'sehir',
            
            -- İlçe adı
            ilce.ilce        AS 'ilce',
            
            -- Bölge sahibi
            COALESCE(yb_ilce.yetki_bolgeleri_bolge_sahibi,
                     yb_il.yetki_bolgeleri_bolge_sahibi) AS 'bolge_sahibi',
            
            -- Sezon adı
            COALESCE(sz_ilce.sezon_adi, sz_il.sezon_adi) AS 'sezon',
            
            -- Sözleşme Hizmeti
            st.stok_ad AS 'sozlesme_hizmeti',
            st.stok_id AS 'stok_id',
            
            -- Sözleşme Tutarı
            sfs.satisStok_fiyatMiktar AS 'sozlesme_tutari_raw',
            FORMAT(sfs.satisStok_fiyatMiktar, 0, 'tr_TR') AS 'sozlesme_tutari',
            
            -- Sözleşme Tarihi
            sf.satis_faturaTarihi AS 'sozlesme_tarihi_raw',
            DATE_FORMAT(sf.satis_faturaTarihi, '%d.%m.%Y') AS 'sozlesme_tarihi',
            
            -- Fatura Durumu
            sfs.satisStok_faturaKesildi AS 'fatura_kesildi',
            CASE 
                WHEN sfs.satisStok_faturaKesildi = 1 THEN 'Kesildi'
                ELSE 'Kesilmedi'
            END AS 'fatura_durumu',
            
            -- Çek Tutarı
            ck.cek_tutar AS 'cek_tutari_raw',
            FORMAT(ck.cek_tutar, 0, 'tr_TR') AS 'cek_tutari',
            
            -- Çek Vade Tarihi
            ck.cek_vadetarih AS 'cek_vade_tarihi_raw',
            DATE_FORMAT(ck.cek_vadetarih, '%d.%m.%Y') AS 'cek_vade_tarihi',
            
            -- Senet Tutarı
            sn.senet_tutar AS 'senet_tutari_raw',
            FORMAT(sn.senet_tutar, 0, 'tr_TR') AS 'senet_tutari',
            
            -- Senet Vade Tarihi
            sn.senet_vadeTarih AS 'senet_vade_tarihi_raw',
            DATE_FORMAT(sn.senet_vadeTarih, '%d.%m.%Y') AS 'senet_vade_tarihi',
            
            -- Tahsilat Nakit Tutar
            kh.kh_giris AS 'tahsilat_nakit_tutar_raw',
            FORMAT(kh.kh_giris, 0, 'tr_TR') AS 'tahsilat_nakit_tutar',
            
            -- Tahsilat Nakit Tarih
            kh.kh_tarih AS 'tahsilat_nakit_tarih_raw',
            DATE_FORMAT(kh.kh_tarih, '%d.%m.%Y') AS 'tahsilat_nakit_tarih',
            
            -- Tahsilat Banka Tutar
            bh.bh_giris AS 'tahsilat_banka_tutar_raw',
            FORMAT(bh.bh_giris, 0, 'tr_TR') AS 'tahsilat_banka_tutar',
            
            -- Tahsilat Banka Tarih
            bh.bh_tarih AS 'tahsilat_banka_tarih_raw',
            DATE_FORMAT(bh.bh_tarih, '%d.%m.%Y') AS 'tahsilat_banka_tarih',
            
            -- Tahsilat Senet Tutar (onaylı)
            FORMAT(sn.senet_tutar, 0, 'tr_TR') AS 'tahsilat_senet_tutar',
            
            -- Tahsilat Senet Tarih
            mtd_sn.islem_tarihi AS 'tahsilat_senet_tarih_raw',
            DATE_FORMAT(mtd_sn.islem_tarihi, '%d.%m.%Y') AS 'tahsilat_senet_tarih',
            
            -- Tahsilat Çek Tutar (onaylı)
            FORMAT(ck.cek_tutar, 0, 'tr_TR') AS 'tahsilat_cek_tutar',
            
            -- Tahsilat Çek Tarih
            mtd_ck.islem_tarihi AS 'tahsilat_cek_tarih_raw',
            DATE_FORMAT(mtd_ck.islem_tarihi, '%d.%m.%Y') AS 'tahsilat_cek_tarih',
            
            -- Personel
            CONCAT(k.kullanici_ad, ' ', k.kullanici_soyad) AS 'personel',
            k.kullanici_id AS 'personel_id',
            
            -- Aktivasyon Üye No
            a.aktivasyon_uye_no AS 'aktivasyon_uye_no',
            
            -- Aktivasyon Hizmet
            sg.stokGrup_ad AS 'aktivasyon_hizmet',
            sg.stokGrup_id AS 'aktivasyon_hizmet_id',
            
            -- Ülke ID (filtreleme için)
            il.ulke_id AS 'ulke_id',
            
            -- İl ID (filtreleme için)
            c.cari_il AS 'il_id',
            
            -- İlçe ID (filtreleme için)
            c.cari_ilce AS 'ilce_id'
            
        FROM cari c
        
        LEFT JOIN iller il 
               ON c.cari_il = il.id
        
        LEFT JOIN ulkeler il_ulke 
               ON il_ulke.id = il.ulke_id
        
        LEFT JOIN ilceler ilce
               ON c.cari_ilce = ilce.id
        
        /* === YETKİ BÖLGELERİ (ÖNCE İLÇE) === */
        LEFT JOIN yetki_bolgeleri yb_ilce
               ON yb_ilce.yetki_bolgeleri_ulke_id = il.ulke_id
              AND yb_ilce.yetki_bolgeleri_il_id   = c.cari_il
              AND yb_ilce.yetki_bolgeleri_ilce_id = c.cari_ilce
        
        LEFT JOIN sezonlar sz_ilce
               ON sz_ilce.sezon_id = yb_ilce.yetki_bolgeleri_sezon_id
        
        /* === YETKİ BÖLGELERİ (İL GENELİ) === */
        LEFT JOIN yetki_bolgeleri yb_il
               ON yb_il.yetki_bolgeleri_ulke_id = il.ulke_id
              AND yb_il.yetki_bolgeleri_il_id   = c.cari_il
              AND yb_il.yetki_bolgeleri_ilce_id IS NULL
        
        LEFT JOIN sezonlar sz_il
               ON sz_il.sezon_id = yb_il.yetki_bolgeleri_sezon_id
        
        /* === SÖZLEŞME / SATIŞ BİLGİLERİ === */
        LEFT JOIN satisfaturasi sf
               ON sf.satis_cariID = c.cari_id
        
        LEFT JOIN satisfaturasistok sfs
               ON sfs.satisStok_satisFaturasiID = sf.satis_id
        
        LEFT JOIN stok st
               ON st.stok_id = sfs.satisStok_stokID
        
        /* === ÇEK BİLGİLERİ === */
        LEFT JOIN cek ck
               ON ck.cek_cariID = c.cari_id
        
        /* === SENET BİLGİLERİ === */
        LEFT JOIN senet sn
               ON sn.senet_cariID = c.cari_id
        
        /* === NAKİT TAHSİLAT (KASA) === */
        LEFT JOIN kasahareketleri kh
               ON kh.kh_cariID = c.cari_id
        
        /* === BANKA TAHSİLAT === */
        LEFT JOIN bankahareketleri bh
               ON bh.bh_cariID = c.cari_id
        
        /* === SENET TAHSİLAT DURUMU (tahsilat_tipi = 4, durum = 1) === */
        LEFT JOIN muhasebe_tahsilat_durum mtd_sn
               ON mtd_sn.kayit_id      = sn.senet_id
              AND mtd_sn.tahsilat_tipi = 4
              AND mtd_sn.durum         = 1
        
        /* === ÇEK TAHSİLAT DURUMU (tahsilat_tipi = 2, durum = 1) === */
        LEFT JOIN muhasebe_tahsilat_durum mtd_ck
               ON mtd_ck.kayit_id      = ck.cek_id
              AND mtd_ck.tahsilat_tipi = 2
              AND mtd_ck.durum         = 1
        
        /* === AKTİVASYON === */
        LEFT JOIN aktivasyon a
               ON a.aktivasyon_cari_id = c.cari_id
        
        /* === AKTİVASYON HİZMET STOK GRUBU === */
        LEFT JOIN stokgruplari sg
               ON sg.stokGrup_id = a.aktivasyon_stok
        
        /* === PERSONEL === */
        LEFT JOIN kullanicilar k
               ON c.cari_olusturan = k.kullanici_id
        
        WHERE $whereClause
        ";
        
        $query = $this->db->query($sql, $params);
        
        // Hata kontrolü
        if (!$query) {
            log_message('error', 'Detaylı rapor sorgusu başarısız: ' . $this->db->error()['message']);
            return false;
        }
        
        return $query;
    }
    
    /**
     * SENARYO 1: Konum Bazlı Satış Özet Raporu
     * 
     * Ülke/İl/İlçe bazında sözleşme hizmeti gruplu özet
     */
    public function get_konum_satis_ozet($filters = [])
    {
        $this->load->helper('destek_helper');
        $anaHesap = anaHesapBilgisi();
        
        $where = ["c.cari_olusturanAnaHesap = $anaHesap"];
        $params = [];
        
        // Filtreler
        if (!empty($filters['baslangic_tarih']) && !empty($filters['bitis_tarih'])) {
            $where[] = "sf.satis_faturaTarihi BETWEEN ? AND ?";
            $params[] = $filters['baslangic_tarih'];
            $params[] = $filters['bitis_tarih'];
        }
        
        if (!empty($filters['ulke_id'])) {
            $where[] = "il.ulke_id = ?";
            $params[] = $filters['ulke_id'];
        }
        
        if (!empty($filters['il_id'])) {
            $where[] = "c.cari_il = ?";
            $params[] = $filters['il_id'];
        }
        
        if (!empty($filters['ilce_id'])) {
            $where[] = "c.cari_ilce = ?";
            $params[] = $filters['ilce_id'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "
        SELECT
            il_ulke.ulke_adi AS 'ulke',
            il.ulke_id AS 'ulke_id',
            il.il AS 'sehir',
            c.cari_il AS 'il_id',
            ilce.ilce AS 'ilce',
            c.cari_ilce AS 'ilce_id',
            st.stok_ad AS 'sozlesme_hizmeti',
            st.stok_id AS 'stok_id',
            COUNT(DISTINCT sf.satis_id) AS 'adet',
            SUM(sfs.satisStok_fiyatMiktar) AS 'toplam_tutar_raw',
            FORMAT(SUM(sfs.satisStok_fiyatMiktar), 0, 'tr_TR') AS 'toplam_tutar'
        FROM cari c
        LEFT JOIN iller il ON c.cari_il = il.id
        LEFT JOIN ulkeler il_ulke ON il_ulke.id = il.ulke_id
        LEFT JOIN ilceler ilce ON c.cari_ilce = ilce.id
        LEFT JOIN satisfaturasi sf ON sf.satis_cariID = c.cari_id
        LEFT JOIN satisfaturasistok sfs ON sfs.satisStok_satisFaturasiID = sf.satis_id
        LEFT JOIN stok st ON st.stok_id = sfs.satisStok_stokID
        WHERE $whereClause
        GROUP BY il.ulke_id, c.cari_il, c.cari_ilce, st.stok_id
        ORDER BY il_ulke.ulke_adi, il.il, ilce.ilce, st.stok_ad
        ";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * SENARYO 2: Personel Bazlı Satış Özet Raporu
     * 
     * Personel bazında sözleşme hizmeti gruplu özet
     */
    public function get_personel_satis_ozet($filters = [])
    {
        $where = ["c.cari_durum = 1"];
        $params = [];
        
        // Filtreler
        if (!empty($filters['baslangic_tarih']) && !empty($filters['bitis_tarih'])) {
            $where[] = "sf.satis_faturaTarihi BETWEEN ? AND ?";
            $params[] = $filters['baslangic_tarih'];
            $params[] = $filters['bitis_tarih'];
        }
        
        if (!empty($filters['personel_id'])) {
            $where[] = "c.cari_olusturan = ?";
            $params[] = $filters['personel_id'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "
        SELECT
            CONCAT(k.kullanici_ad, ' ', k.kullanici_soyad) AS 'personel',
            k.kullanici_id AS 'personel_id',
            st.stok_ad AS 'sozlesme_hizmeti',
            st.stok_id AS 'stok_id',
            COUNT(DISTINCT sf.satis_id) AS 'adet',
            SUM(sfs.satisStok_fiyatMiktar) AS 'toplam_tutar_raw',
            FORMAT(SUM(sfs.satisStok_fiyatMiktar), 0, 'tr_TR') AS 'toplam_tutar'
        FROM cari c
        LEFT JOIN kullanicilar k ON c.cari_olusturan = k.kullanici_id
        LEFT JOIN satisfaturasi sf ON sf.satis_cariID = c.cari_id
        LEFT JOIN satisfaturasistok sfs ON sfs.satisStok_satisFaturasiID = sf.satis_id
        LEFT JOIN stok st ON st.stok_id = sfs.satisStok_stokID
        WHERE $whereClause
        GROUP BY k.kullanici_id, st.stok_id
        ORDER BY personel, st.stok_ad
        ";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * SENARYO 3: Personel Bazlı Tahsilat Özet Raporu
     * 
     * Personel bazında tahsilat türü gruplu özet
     */
    public function get_personel_tahsilat_ozet($filters = [])
    {
        $where = ["c.cari_durum = 1"];
        $params = [];
        
        // Filtreler
        if (!empty($filters['baslangic_tarih']) && !empty($filters['bitis_tarih'])) {
            $where[] = "mtd.olusturma_tarihi BETWEEN ? AND ?";
            $params[] = $filters['baslangic_tarih'];
            $params[] = $filters['bitis_tarih'];
        }
        
        if (!empty($filters['personel_id'])) {
            $where[] = "c.cari_olusturan = ?";
            $params[] = $filters['personel_id'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "
        SELECT
            CONCAT(k.kullanici_ad, ' ', k.kullanici_soyad) AS 'personel',
            k.kullanici_id AS 'personel_id',
            CASE 
                WHEN mtd.tahsilat_tipi = 1 THEN 'Banka'
                WHEN mtd.tahsilat_tipi = 2 THEN 'Çek'
                WHEN mtd.tahsilat_tipi = 3 THEN 'Kasa'
                WHEN mtd.tahsilat_tipi = 4 THEN 'Senet'
                ELSE 'Bilinmiyor'
            END AS 'tahsilat_tipi',
            mtd.tahsilat_tipi AS 'tahsilat_tipi_id',
            COUNT(DISTINCT mtd.id) AS 'adet',
            SUM(
                CASE 
                    WHEN mtd.tahsilat_tipi = 1 THEN COALESCE(bh.bh_giris, 0)
                    WHEN mtd.tahsilat_tipi = 2 THEN COALESCE(ck.cek_tutar, 0)
                    WHEN mtd.tahsilat_tipi = 3 THEN COALESCE(kh.kh_giris, 0)
                    WHEN mtd.tahsilat_tipi = 4 THEN COALESCE(sn.senet_tutar, 0)
                    ELSE 0
                END
            ) AS 'toplam_tutar_raw',
            FORMAT(
                SUM(
                    CASE 
                        WHEN mtd.tahsilat_tipi = 1 THEN COALESCE(bh.bh_giris, 0)
                        WHEN mtd.tahsilat_tipi = 2 THEN COALESCE(ck.cek_tutar, 0)
                        WHEN mtd.tahsilat_tipi = 3 THEN COALESCE(kh.kh_giris, 0)
                        WHEN mtd.tahsilat_tipi = 4 THEN COALESCE(sn.senet_tutar, 0)
                        ELSE 0
                    END
                ), 0, 'tr_TR'
            ) AS 'toplam_tutar'
        FROM muhasebe_tahsilat_durum mtd
        LEFT JOIN bankahareketleri bh ON (mtd.tahsilat_tipi = 1 AND mtd.kayit_id = bh.bh_id)
        LEFT JOIN cek ck ON (mtd.tahsilat_tipi = 2 AND mtd.kayit_id = ck.cek_id)
        LEFT JOIN kasahareketleri kh ON (mtd.tahsilat_tipi = 3 AND mtd.kayit_id = kh.kh_id)
        LEFT JOIN senet sn ON (mtd.tahsilat_tipi = 4 AND mtd.kayit_id = sn.senet_id)
        LEFT JOIN cari c ON (
            (mtd.tahsilat_tipi = 1 AND c.cari_id = bh.bh_cariID) OR
            (mtd.tahsilat_tipi = 2 AND c.cari_id = ck.cek_cariID) OR
            (mtd.tahsilat_tipi = 3 AND c.cari_id = kh.kh_cariID) OR
            (mtd.tahsilat_tipi = 4 AND c.cari_id = sn.senet_cariID)
        )
        LEFT JOIN kullanicilar k ON c.cari_olusturan = k.kullanici_id
        WHERE $whereClause AND mtd.durum = 1
        GROUP BY k.kullanici_id, mtd.tahsilat_tipi
        ORDER BY personel, tahsilat_tipi
        ";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Filtreleme için yardımcı fonksiyonlar
     */
    
    public function get_ulke_listesi()
    {
        return $this->db->query("
            SELECT id AS ulke_id, ulke_adi
            FROM ulkeler
            ORDER BY ulke_adi
        ")->result();
    }
    
    public function get_il_listesi($ulke_id = null)
    {
        $where = $ulke_id ? "WHERE ulke_id = " . intval($ulke_id) : "";
        return $this->db->query("
            SELECT DISTINCT id, il, ulke_id
            FROM iller
            $where
            ORDER BY il
        ")->result();
    }
    
    public function get_ilce_listesi($il_id = null)
    {
        $where = $il_id ? "WHERE il_id = " . intval($il_id) : "";
        return $this->db->query("
            SELECT DISTINCT id, ilce, il_id
            FROM ilceler
            $where
            ORDER BY ilce
        ")->result();
    }
    
    public function get_bolge_sahipleri()
    {
        return $this->db->query("
            SELECT DISTINCT yetki_bolgeleri_bolge_sahibi
            FROM yetki_bolgeleri
            WHERE yetki_bolgeleri_bolge_sahibi IS NOT NULL
            ORDER BY yetki_bolgeleri_bolge_sahibi
        ")->result();
    }
    
    public function get_sezon_listesi()
    {
        return $this->db->query("
            SELECT sezon_id, sezon_adi
            FROM sezonlar
            WHERE sezon_durum = 1
            ORDER BY sezon_adi
        ")->result();
    }
    
    public function get_stok_listesi()
    {
        return $this->db->query("
            SELECT stok_id, stok_ad
            FROM stok
            WHERE stok_durum = 1
            ORDER BY stok_ad
        ")->result();
    }
    
    public function get_personel_listesi()
    {
        return $this->db->query("
            SELECT 
                kullanici_id, 
                kullanici_ad,
                kullanici_soyad,
                CONCAT(kullanici_ad, ' ', kullanici_soyad) AS personel
            FROM kullanicilar
            WHERE kullanici_durum = 1
            ORDER BY kullanici_ad, kullanici_soyad
        ")->result();
    }
    
    public function get_aktivasyon_hizmet_listesi()
    {
        return $this->db->query("
            SELECT stokGrup_id, stokGrup_ad
            FROM stokgruplari
            ORDER BY stokGrup_ad
        ")->result();
    }
}
