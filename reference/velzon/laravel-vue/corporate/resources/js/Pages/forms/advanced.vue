<script>
import Toggle from "@vueform/toggle";
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";

import us_flag from "@assets/images/flags/us.svg";
import ac_flag from "@assets/images/flags/ac.svg"
import ad_flag from "@assets/images/flags/ad.svg"
import ae_flag from "@assets/images/flags/ae.svg"
import af_flag from "@assets/images/flags/af.svg"
import ag_flag from "@assets/images/flags/ag.svg"
import ai_flag from "@assets/images/flags/ai.svg"
import am_flag from "@assets/images/flags/am.svg"
import ao_flag from "@assets/images/flags/ao.svg"
import aq_flag from "@assets/images/flags/aq.svg"
import ar_flag from "@assets/images/flags/ar.svg"
import as_flag from "@assets/images/flags/as.svg"
import at_flag from "@assets/images/flags/at.svg"
import au_flag from "@assets/images/flags/au.svg"
import aw_flag from "@assets/images/flags/aw.svg"
import ax_flag from "@assets/images/flags/ax.svg"
import ba_flag from "@assets/images/flags/ba.svg"
import bb_flag from "@assets/images/flags/bb.svg"
import bd_flag from "@assets/images/flags/bd.svg"
import be_flag from "@assets/images/flags/be.svg"
import bf_flag from "@assets/images/flags/bf.svg"
import bh_flag from "@assets/images/flags/bh.svg"
import bi_flag from "@assets/images/flags/bi.svg"
import bj_flag from "@assets/images/flags/bj.svg"
import bl_flag from "@assets/images/flags/bl.svg"
import bm_flag from "@assets/images/flags/bm.svg"
import bn_flag from "@assets/images/flags/bn.svg"
import bo_flag from "@assets/images/flags/bo.svg"
import bq_flag from "@assets/images/flags/bq.svg"
import br_flag from "@assets/images/flags/br.svg"
import bs_flag from "@assets/images/flags/bs.svg"
import bt_flag from "@assets/images/flags/bt.svg"
import bv_flag from "@assets/images/flags/bv.svg"
import bw_flag from "@assets/images/flags/bw.svg"
import by_flag from "@assets/images/flags/by.svg"
import bz_flag from "@assets/images/flags/bz.svg"
import ca_flag from "@assets/images/flags/ca.svg"
import cc_flag from "@assets/images/flags/cc.svg"
import cd_flag from "@assets/images/flags/cd.svg"
import cf_flag from "@assets/images/flags/cf.svg"
import cg_flag from "@assets/images/flags/cg.svg"
import ch_flag from "@assets/images/flags/ch.svg"
import ci_flag from "@assets/images/flags/ci.svg"
import ck_flag from "@assets/images/flags/ck.svg"
import cl_flag from "@assets/images/flags/cl.svg"
import cm_flag from "@assets/images/flags/cm.svg"
import cn_flag from "@assets/images/flags/cn.svg"
import co_flag from "@assets/images/flags/co.svg"
import cp_flag from "@assets/images/flags/cp.svg"
import cr_flag from "@assets/images/flags/cr.svg"
import cu_flag from "@assets/images/flags/cu.svg"
import cv_flag from "@assets/images/flags/cv.svg"
import cw_flag from "@assets/images/flags/cw.svg"
import cx_flag from "@assets/images/flags/cx.svg"
import cy_flag from "@assets/images/flags/cy.svg"
import cz_flag from "@assets/images/flags/cz.svg"
import de_flag from "@assets/images/flags/de.svg"
import dg_flag from "@assets/images/flags/dg.svg"
import dj_flag from "@assets/images/flags/dj.svg"
import dk_flag from "@assets/images/flags/dk.svg"
import dm_flag from "@assets/images/flags/dm.svg"
import do_flag from "@assets/images/flags/do.svg"
import dz_flag from "@assets/images/flags/dz.svg"
import ea_flag from "@assets/images/flags/ea.svg"
import ec_flag from "@assets/images/flags/ec.svg"
import ee_flag from "@assets/images/flags/ee.svg"
import eg_flag from "@assets/images/flags/eg.svg"
import eh_flag from "@assets/images/flags/eh.svg"
import er_flag from "@assets/images/flags/er.svg"
import es_flag from "@assets/images/flags/es.svg"
import esct_flag from "@assets/images/flags/es-ct.svg"
import esga_flag from "@assets/images/flags/es-ga.svg"
import et_flag from "@assets/images/flags/et.svg"
import eu_flag from "@assets/images/flags/eu.svg"
import fi_flag from "@assets/images/flags/fi.svg"
import fj_flag from "@assets/images/flags/fj.svg"
import fk_flag from "@assets/images/flags/fk.svg"
import fm_flag from "@assets/images/flags/fm.svg"
import fo_flag from "@assets/images/flags/fo.svg"
import fr_flag from "@assets/images/flags/fr.svg"
import ga_flag from "@assets/images/flags/ga.svg"
import gbeng_flag from "@assets/images/flags/gb-eng.svg"
import gbnir_flag from "@assets/images/flags/gb-nir.svg"
import gbsct_flag from "@assets/images/flags/gb-sct.svg"
import gbwls_flag from "@assets/images/flags/gb-wls.svg"
import gd_flag from "@assets/images/flags/gd.svg"
import ge_flag from "@assets/images/flags/ge.svg"
import gf_flag from "@assets/images/flags/gf.svg"
import gg_flag from "@assets/images/flags/gg.svg"
import gh_flag from "@assets/images/flags/gh.svg"
import gi_flag from "@assets/images/flags/gi.svg"
import gl_flag from "@assets/images/flags/gl.svg"
import gm_flag from "@assets/images/flags/gm.svg"
import gn_flag from "@assets/images/flags/gn.svg"
import gp_flag from "@assets/images/flags/gp.svg"
import gq_flag from "@assets/images/flags/gq.svg"
import gr_flag from "@assets/images/flags/gr.svg"
import gs_flag from "@assets/images/flags/gs.svg"
import gt_flag from "@assets/images/flags/gt.svg"
import gu_flag from "@assets/images/flags/gu.svg"
import gw_flag from "@assets/images/flags/gw.svg"
import gy_flag from "@assets/images/flags/gy.svg"
import hk_flag from "@assets/images/flags/hk.svg"
import hn_flag from "@assets/images/flags/hn.svg"
import hr_flag from "@assets/images/flags/hr.svg"
import ht_flag from "@assets/images/flags/ht.svg"
import hu_flag from "@assets/images/flags/hu.svg"
import id_flag from "@assets/images/flags/id.svg"
import ie_flag from "@assets/images/flags/ie.svg"
import il_flag from "@assets/images/flags/il.svg"
import im_flag from "@assets/images/flags/im.svg"
import in_flag from "@assets/images/flags/in.svg"
import io_flag from "@assets/images/flags/io.svg"
import iq_flag from "@assets/images/flags/iq.svg"
import ir_flag from "@assets/images/flags/ir.svg"
import is_flag from "@assets/images/flags/is.svg"
import it_flag from "@assets/images/flags/it.svg"
import je_flag from "@assets/images/flags/je.svg"
import jm_flag from "@assets/images/flags/jm.svg"
import jo_flag from "@assets/images/flags/jo.svg"
import jp_flag from "@assets/images/flags/jp.svg"
import ke_flag from "@assets/images/flags/ke.svg"
import kg_flag from "@assets/images/flags/kg.svg"
import kh_flag from "@assets/images/flags/kh.svg"
import ki_flag from "@assets/images/flags/ki.svg"
import km_flag from "@assets/images/flags/km.svg"
import kn_flag from "@assets/images/flags/kn.svg"
import kp_flag from "@assets/images/flags/kp.svg"
import kr_flag from "@assets/images/flags/kr.svg"
import kw_flag from "@assets/images/flags/kw.svg"
import ky_flag from "@assets/images/flags/ky.svg"
import kz_flag from "@assets/images/flags/kz.svg"
import la_flag from "@assets/images/flags/la.svg"
import lb_flag from "@assets/images/flags/lb.svg"
import lc_flag from "@assets/images/flags/lc.svg"
import li_flag from "@assets/images/flags/li.svg"
import lk_flag from "@assets/images/flags/lk.svg"
import lr_flag from "@assets/images/flags/lr.svg"
import ls_flag from "@assets/images/flags/ls.svg"
import lt_flag from "@assets/images/flags/lt.svg"
import lu_flag from "@assets/images/flags/lu.svg"
import lv_flag from "@assets/images/flags/lv.svg"
import ly_flag from "@assets/images/flags/ly.svg"
import ma_flag from "@assets/images/flags/ma.svg"
import mc_flag from "@assets/images/flags/mc.svg"
import md_flag from "@assets/images/flags/md.svg"
import me_flag from "@assets/images/flags/me.svg"
import mf_flag from "@assets/images/flags/mf.svg"
import mg_flag from "@assets/images/flags/mg.svg"
import mh_flag from "@assets/images/flags/mh.svg"
import mk_flag from "@assets/images/flags/mk.svg"
import ml_flag from "@assets/images/flags/ml.svg"
import mm_flag from "@assets/images/flags/mm.svg"
import mn_flag from "@assets/images/flags/mn.svg"
import mo_flag from "@assets/images/flags/mo.svg"
import mp_flag from "@assets/images/flags/mp.svg"
import mq_flag from "@assets/images/flags/mq.svg"
import mr_flag from "@assets/images/flags/mr.svg"
import ms_flag from "@assets/images/flags/ms.svg"
import mt_flag from "@assets/images/flags/mt.svg"
import mu_flag from "@assets/images/flags/mu.svg"
import mv_flag from "@assets/images/flags/mv.svg"
import mw_flag from "@assets/images/flags/mw.svg"
import mx_flag from "@assets/images/flags/mx.svg"
import my_flag from "@assets/images/flags/my.svg"
import mz_flag from "@assets/images/flags/mz.svg"
import na_flag from "@assets/images/flags/na.svg"
import nc_flag from "@assets/images/flags/nc.svg"
import ne_flag from "@assets/images/flags/ne.svg"
import nf_flag from "@assets/images/flags/nf.svg"
import ng_flag from "@assets/images/flags/ng.svg"
import ni_flag from "@assets/images/flags/ni.svg"
import nl_flag from "@assets/images/flags/nl.svg"
import no_flag from "@assets/images/flags/no.svg"
import np_flag from "@assets/images/flags/np.svg"
import nr_flag from "@assets/images/flags/nr.svg"
import nu_flag from "@assets/images/flags/nu.svg"
import nz_flag from "@assets/images/flags/nz.svg"
import om_flag from "@assets/images/flags/om.svg"
import pa_flag from "@assets/images/flags/pa.svg"
import pe_flag from "@assets/images/flags/pe.svg"
import pf_flag from "@assets/images/flags/pf.svg"
import pg_flag from "@assets/images/flags/pg.svg"
import ph_flag from "@assets/images/flags/ph.svg"
import pk_flag from "@assets/images/flags/pk.svg"
import pl_flag from "@assets/images/flags/pl.svg"
import pm_flag from "@assets/images/flags/pm.svg"
import pn_flag from "@assets/images/flags/pn.svg"
import pr_flag from "@assets/images/flags/pr.svg"
import ps_flag from "@assets/images/flags/ps.svg"
import pt_flag from "@assets/images/flags/pt.svg"
import pw_flag from "@assets/images/flags/pw.svg"
import py_flag from "@assets/images/flags/py.svg"
import qa_flag from "@assets/images/flags/qa.svg"
import re_flag from "@assets/images/flags/re.svg"
import ro_flag from "@assets/images/flags/ro.svg"
import rs_flag from "@assets/images/flags/rs.svg"
import ru_flag from "@assets/images/flags/ru.svg"
import rw_flag from "@assets/images/flags/rw.svg"
import sa_flag from "@assets/images/flags/sa.svg"
import sb_flag from "@assets/images/flags/sb.svg"
import sc_flag from "@assets/images/flags/sc.svg"
import sd_flag from "@assets/images/flags/sd.svg"
import se_flag from "@assets/images/flags/se.svg"
import sg_flag from "@assets/images/flags/sg.svg"
import si_flag from "@assets/images/flags/si.svg"
import sj_flag from "@assets/images/flags/sj.svg"
import sk_flag from "@assets/images/flags/sk.svg"
import sl_flag from "@assets/images/flags/sl.svg"
import sm_flag from "@assets/images/flags/sm.svg"
import sn_flag from "@assets/images/flags/sn.svg"
import so_flag from "@assets/images/flags/so.svg"
import sr_flag from "@assets/images/flags/sr.svg"
import ss_flag from "@assets/images/flags/ss.svg"
import st_flag from "@assets/images/flags/st.svg"
import sv_flag from "@assets/images/flags/sv.svg"
import sx_flag from "@assets/images/flags/sx.svg"
import sy_flag from "@assets/images/flags/sy.svg"
import sz_flag from "@assets/images/flags/sz.svg"
import ta_flag from "@assets/images/flags/ta.svg"
import tc_flag from "@assets/images/flags/tc.svg"
import td_flag from "@assets/images/flags/td.svg"
import tg_flag from "@assets/images/flags/tg.svg"
import th_flag from "@assets/images/flags/th.svg"
import tj_flag from "@assets/images/flags/tj.svg"
import tk_flag from "@assets/images/flags/tk.svg"
import tl_flag from "@assets/images/flags/tl.svg"
import tm_flag from "@assets/images/flags/tm.svg"
import tn_flag from "@assets/images/flags/tn.svg"
import to_flag from "@assets/images/flags/to.svg"
import tr_flag from "@assets/images/flags/tr.svg"
import tt_flag from "@assets/images/flags/tt.svg"
import tv_flag from "@assets/images/flags/tv.svg"
import tw_flag from "@assets/images/flags/tw.svg"
import tz_flag from "@assets/images/flags/tz.svg"
import ua_flag from "@assets/images/flags/ua.svg"
import ug_flag from "@assets/images/flags/ug.svg"
import uy_flag from "@assets/images/flags/uy.svg"
import uz_flag from "@assets/images/flags/uz.svg"
import va_flag from "@assets/images/flags/va.svg"
import vc_flag from "@assets/images/flags/vc.svg"
import ve_flag from "@assets/images/flags/ve.svg"
import vg_flag from "@assets/images/flags/vg.svg"
import vi_flag from "@assets/images/flags/vi.svg"
import vn_flag from "@assets/images/flags/vn.svg"
import vu_flag from "@assets/images/flags/vu.svg"
import wf_flag from "@assets/images/flags/wf.svg"
import ws_flag from "@assets/images/flags/ws.svg"
import xk_flag from "@assets/images/flags/xk.svg"
import ye_flag from "@assets/images/flags/ye.svg"
import yt_flag from "@assets/images/flags/yt.svg"
import za_flag from "@assets/images/flags/za.svg"
import zm_flag from "@assets/images/flags/zm.svg"
import zw_flag from "@assets/images/flags/zw.svg"

const sampleCountryData = {
  flagImg: us_flag,
  countryName: "United States of America",
  countryCode: "+1"
};
export default {
  data() {
    return {
      value: true,
      value1: true,
      value2: true,
      value3: true,
      value4: true,
      maxlen: "",
      data: "",
      option: "",
      textarea: "",
      defaultNull: null,
      defaultspinval: 1,
      countrylist: [
        {
          id: 1,
          flagImg: ac_flag,
          countryName: "Ascension Island",
          countryCode: "+247"
        },
        {
          id: 2,
          flagImg: ad_flag,
          countryName: "Andorra",
          countryCode: "+376"
        },
        {
          id: 3,
          flagImg: ae_flag,
          countryName: "United Arab Emirates",
          countryCode: "+971"
        },
        {
          id: 4,
          flagImg: af_flag,
          countryName: "Afghanistan",
          countryCode: "+93"
        },
        {
          id: 5,
          flagImg: ag_flag,
          countryName: "Antigua and Barbuda",
          countryCode: "+93"
        },
        {
          id: 6,
          flagImg: ai_flag,
          countryName: "Anguilla",
          countryCode: "+1"
        },
        {
          id: 7,
          flagImg: am_flag,
          countryName: "Armenia",
          countryCode: "+374"
        },
        {
          id: 8,
          flagImg: ao_flag,
          countryName: "Angola",
          countryCode: "+244"
        },
        {
          id: 9,
          flagImg: aq_flag,
          countryName: "Antarctica",
          countryCode: "+672"
        },
        {
          id: 10,
          flagImg: ar_flag,
          countryName: "Argentina",
          countryCode: "+54"
        },
        {
          id: 11,
          flagImg: as_flag,
          countryName: "American Samoa",
          countryCode: "+1"
        },
        {
          id: 12,
          flagImg: at_flag,
          countryName: "Austria",
          countryCode: "+43"
        },
        {
          id: 13,
          flagImg: au_flag,
          countryName: "Australia",
          countryCode: "+61"
        },
        {
          id: 14,
          flagImg: aw_flag,
          countryName: "Aruba",
          countryCode: "+297"
        },
        {
          id: 15,
          flagImg: ax_flag,
          countryName: "Aland Islands",
          countryCode: "+358"
        },
        {
          id: 16,
          flagImg: ba_flag,
          countryName: "Bosnia and Herzegovina",
          countryCode: "+387"
        },
        {
          id: 17,
          flagImg: bb_flag,
          countryName: "Barbados",
          countryCode: "+1"
        },
        {
          id: 18,
          flagImg: bd_flag,
          countryName: "Bangladesh",
          countryCode: "+880"
        },
        {
          id: 19,
          flagImg: be_flag,
          countryName: "Belgium",
          countryCode: "+32"
        },
        {
          id: 20,
          flagImg: bf_flag,
          countryName: "Burkina Faso",
          countryCode: "+226"
        },
        {
          id: 21,
          flagImg: bf_flag,
          countryName: "Bulgaria",
          countryCode: "+359"
        },
        {
          id: 22,
          flagImg: bh_flag,
          countryName: "Bahrain",
          countryCode: "+973"
        },
        {
          id: 23,
          flagImg: bi_flag,
          countryName: "Burundi",
          countryCode: "+257"
        },
        {
          id: 24,
          flagImg: bj_flag,
          countryName: "Benin",
          countryCode: "+229"
        },
        {
          id: 25,
          flagImg: bl_flag,
          countryName: "Saint Barthélemy",
          countryCode: "+590"
        },
        {
          id: 26,
          flagImg: bm_flag,
          countryName: "Bermuda",
          countryCode: "+1"
        },
        {
          id: 27,
          flagImg: bn_flag,
          countryName: "Brunei Darussalam",
          countryCode: "+673"
        },
        {
          id: 28,
          flagImg: bo_flag,
          countryName: "Bolivia (Plurinational State of)",
          countryCode: "+591"
        },
        {
          id: 29,
          flagImg: bq_flag,
          countryName: "Bonaire, Sint Eustatius and Saba",
          countryCode: "+599"
        },
        {
          id: 30,
          flagImg: br_flag,
          countryName: "Brazil",
          countryCode: "+55"
        },
        {
          id: 31,
          flagImg: bs_flag,
          countryName: "Bahamas",
          countryCode: "+1"
        },
        {
          id: 32,
          flagImg: bt_flag,
          countryName: "Bhutan",
          countryCode: "+975"
        },
        {
          id: 33,
          flagImg: bv_flag,
          countryName: "Bouvet Island",
          countryCode: "+47"
        },
        {
          id: 34,
          flagImg: bw_flag,
          countryName: "Botswana",
          countryCode: "+267"
        },
        {
          id: 35,
          flagImg: by_flag,
          countryName: "Belarus",
          countryCode: "+375"
        },
        {
          id: 36,
          flagImg: bz_flag,
          countryName: "Belize",
          countryCode: "+501"
        },
        {
          id: 37,
          flagImg: ca_flag,
          countryName: "Canada",
          countryCode: "+1"
        },
        {
          id: 38,
          flagImg: cc_flag,
          countryName: "Cocos (Keeling) Island",
          countryCode: "+61"
        },
        {
          id: 39,
          flagImg: cd_flag,
          countryName: "Democratic Republic of the Congo",
          countryCode: "+243"
        },
        {
          id: 40,
          flagImg: cf_flag,
          countryName: "Central African Republic",
          countryCode: "+236"
        },
        {
          id: 41,
          flagImg: cg_flag,
          countryName: "Republic of the Congo",
          countryCode: "+243"
        },
        {
          id: 42,
          flagImg: ch_flag,
          countryName: "Switzerland",
          countryCode: "+41"
        },
        {
          id: 43,
          flagImg: ci_flag,
          countryName: "Ivory Coast",
          countryCode: "+225"
        },
        {
          id: 44,
          flagImg: ck_flag,
          countryName: "Cook Islands",
          countryCode: "+682"
        },
        {
          id: 45,
          flagImg: cl_flag,
          countryName: "Chile",
          countryCode: "+56"
        },
        {
          id: 46,
          flagImg: cm_flag,
          countryName: "Cameroon",
          countryCode: "+237"
        },
        {
          id: 47,
          flagImg: cn_flag,
          countryName: "China",
          countryCode: "+86"
        },
        {
          id: 48,
          flagImg: co_flag,
          countryName: "Colombia",
          countryCode: "+57"
        },
        {
          id: 49,
          flagImg: cp_flag,
          countryName: "Clipperton Island",
          countryCode: "+55"
        },
        {
          id: 50,
          flagImg: cr_flag,
          countryName: "Costa Rica",
          countryCode: "+506"
        },
        {
          id: 51,
          flagImg: cu_flag,
          countryName: "Cuba",
          countryCode: "+53"
        },
        {
          id: 52,
          flagImg: cv_flag,
          countryName: "Cabo Verde",
          countryCode: "+238"
        },
        {
          id: 53,
          flagImg: cw_flag,
          countryName: "Curaçao",
          countryCode: "+599"
        },
        {
          id: 54,
          flagImg: cx_flag,
          countryName: "Christmas Island",
          countryCode: "+61"
        },
        {
          id: 55,
          flagImg: cy_flag,
          countryName: "Cyprus",
          countryCode: "+357"
        },
        {
          id: 56,
          flagImg: cz_flag,
          countryName: "Czech Republic",
          countryCode: "+420"
        },
        {
          id: 57,
          flagImg: de_flag,
          countryName: "Germany",
          countryCode: "+49"
        },
        {
          id: 58,
          flagImg: dg_flag,
          countryName: "Diego Garcia",
          countryCode: "+246"
        },
        {
          id: 59,
          flagImg: dj_flag,
          countryName: "Djibouti",
          countryCode: "+253"
        },
        {
          id: 60,
          flagImg: dk_flag,
          countryName: "Denmark",
          countryCode: "+45"
        },
        {
          id: 61,
          flagImg: dm_flag,
          countryName: "Dominica",
          countryCode: "+1"
        },
        {
          id: 62,
          flagImg: do_flag,
          countryName: "Dominican Republic",
          countryCode: "+1"
        },
        {
          id: 63,
          flagImg: dz_flag,
          countryName: "Algeria",
          countryCode: "+213"
        },
        {
          id: 64,
          flagImg: ea_flag,
          countryName: "Ceuta & Melilla",
          countryCode: "34"
        },
        {
          id: 65,
          flagImg: ec_flag,
          countryName: "Ecuador",
          countryCode: "+593"
        },
        {
          id: 66,
          flagImg: ee_flag,
          countryName: "Estonia",
          countryCode: "+372"
        },
        {
          id: 67,
          flagImg: eg_flag,
          countryName: "Egypt",
          countryCode: "+20"
        },
        {
          id: 68,
          flagImg: eh_flag,
          countryName: "Western Sahara",
          countryCode: "+212"
        },
        {
          id: 69,
          flagImg: er_flag,
          countryName: "Eritrea",
          countryCode: "+291"
        },
        {
          id: 70,
          flagImg: es_flag,
          countryName: "Spain",
          countryCode: "+34"
        },
        {
          id: 71,
          flagImg: es_flag,
          countryName: "Catalonia",
          countryCode: "+34"
        },
        {
          id: 72,
          flagImg: es_flag,
          countryName: "Galicia",
          countryCode: "+34"
        },
        {
          id: 73,
          flagImg: et_flag,
          countryName: "Ethiopia",
          countryCode: "+251"
        },
        {
          id: 74,
          flagImg: eu_flag,
          countryName: "Europe",
          countryCode: "+3"
        },
        {
          id: 75,
          flagImg: fi_flag,
          countryName: "Finland",
          countryCode: "+358"
        },
        {
          id: 76,
          flagImg: fj_flag,
          countryName: "Fiji",
          countryCode: "+679"
        },
        {
          id: 77,
          flagImg: fk_flag,
          countryName: "Falkland Islands",
          countryCode: "+500"
        },
        {
          id: 78,
          flagImg: fm_flag,
          countryName: "Federated States of Micronesia",
          countryCode: "+691"
        },
        {
          id: 79,
          flagImg: fo_flag,
          countryName: "Faroe Islands",
          countryCode: "+298"
        },
        {
          id: 80,
          flagImg: fr_flag,
          countryName: "France",
          countryCode: "+33"
        },
        {
          id: 81,
          flagImg: ga_flag,
          countryName: "Gabon",
          countryCode: "+241"
        },
        {
          id: 82,
          flagImg: gbeng_flag,
          countryName: "England",
          countryCode: "+44"
        },
        {
          id: 83,
          flagImg: gbnir_flag,
          countryName: "Northern Ireland",
          countryCode: "+44"
        },
        {
          id: 84,
          flagImg: gbsct_flag,
          countryName: "Scotland",
          countryCode: "+44"
        },
        {
          id: 85,
          flagImg: gbwls_flag,
          countryName: "Wales",
          countryCode: "+44"
        },
        {
          id: 86,
          flagImg: gd_flag,
          countryName: "Grenada",
          countryCode: "+1"
        },
        {
          id: 87,
          flagImg: ge_flag,
          countryName: "Georgia",
          countryCode: "+995"
        },
        {
          id: 88,
          flagImg: gf_flag,
          countryName: "French Guiana",
          countryCode: "+594"
        },
        {
          id: 99,
          flagImg: gg_flag,
          countryName: "Guernsey",
          countryCode: "+44"
        },
        {
          id: 90,
          flagImg: gh_flag,
          countryName: "Ghana",
          countryCode: "+233"
        },
        {
          id: 91,
          flagImg: gi_flag,
          countryName: "Gibraltar",
          countryCode: "+350"
        },
        {
          id: 92,
          flagImg: gl_flag,
          countryName: "Greenland",
          countryCode: "+299"
        },
        {
          id: 93,
          flagImg: gm_flag,
          countryName: "Gambia",
          countryCode: "+220"
        },
        {
          id: 94,
          flagImg: gn_flag,
          countryName: "Guinea",
          countryCode: "+224"
        },
        {
          id: 95,
          flagImg: gp_flag,
          countryName: "Guadeloupe",
          countryCode: "+590"
        },
        {
          id: 96,
          flagImg: gq_flag,
          countryName: "Equatorial Guinea",
          countryCode: "+240"
        },
        {
          id: 97,
          flagImg: gr_flag,
          countryName: "Greece",
          countryCode: "+30"
        },
        {
          id: 98,
          flagImg: gs_flag,
          countryName: "South Georgia and the South Sandwich Islands",
          countryCode: "+500"
        },
        {
          id: 99,
          flagImg: gt_flag,
          countryName: "Guatemala",
          countryCode: "+502"
        },
        {
          id: 100,
          flagImg: gu_flag,
          countryName: "Guam",
          countryCode: "+1"
        },
        {
          id: 101,
          flagImg: gw_flag,
          countryName: "Guinea-Bissau",
          countryCode: "+245"
        },
        {
          id: 102,
          flagImg: gy_flag,
          countryName: "Guyana",
          countryCode: "+592"
        },
        {
          id: 103,
          flagImg: hk_flag,
          countryName: "Hong Kong",
          countryCode: "+852"
        },
        {
          id: 104,
          flagImg: hn_flag,
          countryName: "Honduras",
          countryCode: "+504"
        },
        {
          id: 105,
          flagImg: hr_flag,
          countryName: "Croatia",
          countryCode: "+385"
        },
        {
          id: 106,
          flagImg: ht_flag,
          countryName: "Haiti",
          countryCode: "+509"
        },
        {
          id: 107,
          flagImg: hu_flag,
          countryName: "Hungary",
          countryCode: "+36"
        },
        {
          id: 108,
          flagImg: id_flag,
          countryName: "Indonesia",
          countryCode: "+62"
        },
        {
          id: 109,
          flagImg: ie_flag,
          countryName: "Ireland",
          countryCode: "+353"
        },
        {
          id: 110,
          flagImg: il_flag,
          countryName: "Israel",
          countryCode: "+972"
        },
        {
          id: 111,
          flagImg: im_flag,
          countryName: "Isle of Man",
          countryCode: "+44"
        },
        {
          id: 112,
          flagImg: in_flag,
          countryName: "India",
          countryCode: "+91"
        },
        {
          id: 113,
          flagImg: io_flag,
          countryName: "British Indian Ocean Territory",
          countryCode: "+246"
        },
        {
          id: 114,
          flagImg: iq_flag,
          countryName: "Iraq",
          countryCode: "+964"
        },
        {
          id: 115,
          flagImg: ir_flag,
          countryName: "Iran (Islamic Republic of)",
          countryCode: "+98"
        },
        {
          id: 116,
          flagImg: is_flag,
          countryName: "Iceland",
          countryCode: "+354"
        },
        {
          id: 117,
          flagImg: it_flag,
          countryName: "Italy",
          countryCode: "+39"
        },
        {
          id: 118,
          flagImg: je_flag,
          countryName: "Jersey",
          countryCode: "+44"
        },
        {
          id: 119,
          flagImg: jm_flag,
          countryName: "Jamaica",
          countryCode: "+1"
        },
        {
          id: 120,
          flagImg: jo_flag,
          countryName: "Jordan",
          countryCode: "+962"
        },
        {
          id: 121,
          flagImg: jp_flag,
          countryName: "Japan",
          countryCode: "+81"
        },
        {
          id: 122,
          flagImg: ke_flag,
          countryName: "Kenya",
          countryCode: "+254"
        },
        {
          id: 123,
          flagImg: kg_flag,
          countryName: "Kyrgyzstan",
          countryCode: "+996"
        },
        {
          id: 124,
          flagImg: kh_flag,
          countryName: "Cambodia",
          countryCode: "+855"
        },
        {
          id: 125,
          flagImg: ki_flag,
          countryName: "Kiribati",
          countryCode: "+686"
        },
        {
          id: 126,
          flagImg: km_flag,
          countryName: "Comoros",
          countryCode: "+269"
        },
        {
          id: 127,
          flagImg: kn_flag,
          countryName: "Saint Kitts and Nevis",
          countryCode: "+1"
        },
        {
          id: 128,
          flagImg: kp_flag,
          countryName: "North Korea",
          countryCode: "+850"
        },
        {
          id: 129,
          flagImg: kr_flag,
          countryName: "South Korea",
          countryCode: "+82"
        },
        {
          id: 130,
          flagImg: kw_flag,
          countryName: "Kuwait",
          countryCode: "+965"
        },
        {
          id: 131,
          flagImg: ky_flag,
          countryName: "Cayman Islands",
          countryCode: "+1"
        },
        {
          id: 132,
          flagImg: kz_flag,
          countryName: "Kazakhstan",
          countryCode: "+7"
        },
        {
          id: 133,
          flagImg: la_flag,
          countryName: "Laos",
          countryCode: "+856"
        },
        {
          id: 134,
          flagImg: lb_flag,
          countryName: "Lebanon",
          countryCode: "+961"
        },
        {
          id: 135,
          flagImg: lc_flag,
          countryName: "Saint Lucia",
          countryCode: "+1"
        },
        {
          id: 136,
          flagImg: li_flag,
          countryName: "Liechtenstein",
          countryCode: "+423"
        },
        {
          id: 137,
          flagImg: lk_flag,
          countryName: "Sri Lanka",
          countryCode: "+94"
        },
        {
          id: 138,
          flagImg: lr_flag,
          countryName: "Liberia",
          countryCode: "+231"
        },
        {
          id: 139,
          flagImg: ls_flag,
          countryName: "Lesotho",
          countryCode: "+266"
        },
        {
          id: 140,
          flagImg: lt_flag,
          countryName: "Lithuania",
          countryCode: "+370"
        },
        {
          id: 141,
          flagImg: lu_flag,
          countryName: "Luxembourg",
          countryCode: "+352"
        },
        {
          id: 142,
          flagImg: lv_flag,
          countryName: "Latvia",
          countryCode: "+371"
        },
        {
          id: 143,
          flagImg: ly_flag,
          countryName: "Libya",
          countryCode: "+218"
        },
        {
          id: 144,
          flagImg: ma_flag,
          countryName: "Morocco",
          countryCode: "+212"
        },
        {
          id: 145,
          flagImg: mc_flag,
          countryName: "Monaco",
          countryCode: "+377"
        },
        {
          id: 146,
          flagImg: md_flag,
          countryName: "Moldova",
          countryCode: "+373"
        },
        {
          id: 147,
          flagImg: me_flag,
          countryName: "Montenegro",
          countryCode: "+382"
        },
        {
          id: 148,
          flagImg: mf_flag,
          countryName: "Saint Martin",
          countryCode: "+721"
        },
        {
          id: 149,
          flagImg: mg_flag,
          countryName: "Madagascar",
          countryCode: "+261"
        },
        {
          id: 150,
          flagImg: mh_flag,
          countryName: "Marshall Islands",
          countryCode: "+692"
        },
        {
          id: 151,
          flagImg: mk_flag,
          countryName: "North Macedonia",
          countryCode: "+389"
        },
        {
          id: 152,
          flagImg: ml_flag,
          countryName: "Mali",
          countryCode: "+223"
        },
        {
          id: 153,
          flagImg: mm_flag,
          countryName: "Myanmar",
          countryCode: "+95"
        },
        {
          id: 154,
          flagImg: mn_flag,
          countryName: "Mongolia",
          countryCode: "+976"
        },
        {
          id: 155,
          flagImg: mo_flag,
          countryName: "Macau",
          countryCode: "+853"
        },
        {
          id: 156,
          flagImg: mp_flag,
          countryName: "Northern Mariana Islands",
          countryCode: "+1"
        },
        {
          id: 157,
          flagImg: mq_flag,
          countryName: "Martinique",
          countryCode: "+596"
        },
        {
          id: 158,
          flagImg: mr_flag,
          countryName: "Mauritania",
          countryCode: "+222"
        },
        {
          id: 159,
          flagImg: ms_flag,
          countryName: "Montserrat",
          countryCode: "+1"
        },
        {
          id: 160,
          flagImg: mt_flag,
          countryName: "Malta",
          countryCode: "+356"
        },
        {
          id: 161,
          flagImg: mu_flag,
          countryName: "Mauritius",
          countryCode: "+230"
        },
        {
          id: 162,
          flagImg: mv_flag,
          countryName: "Maldives",
          countryCode: "+960"
        },
        {
          id: 163,
          flagImg: mw_flag,
          countryName: "Malawi",
          countryCode: "+265"
        },
        {
          id: 164,
          flagImg: mx_flag,
          countryName: "Mexico",
          countryCode: "+52"
        },
        {
          id: 165,
          flagImg: my_flag,
          countryName: "Malaysia",
          countryCode: "+60"
        },
        {
          id: 166,
          flagImg: mz_flag,
          countryName: "Mozambique",
          countryCode: "+258"
        },
        {
          id: 167,
          flagImg: na_flag,
          countryName: "Namibia",
          countryCode: "+264"
        },
        {
          id: 168,
          flagImg: nc_flag,
          countryName: "New Caledonia",
          countryCode: "+687"
        },
        {
          id: 169,
          flagImg: ne_flag,
          countryName: "Niger",
          countryCode: "+227"
        },
        {
          id: 170,
          flagImg: nf_flag,
          countryName: "Norfolk Island",
          countryCode: "+672"
        },
        {
          id: 171,
          flagImg: ng_flag,
          countryName: "Nigeria",
          countryCode: "+234"
        },
        {
          id: 172,
          flagImg: ni_flag,
          countryName: "Nicaragua",
          countryCode: "+505"
        },
        {
          id: 173,
          flagImg: nl_flag,
          countryName: "Netherlands",
          countryCode: "+31"
        },
        {
          id: 174,
          flagImg: no_flag,
          countryName: "Norway",
          countryCode: "+47"
        },
        {
          id: 175,
          flagImg: np_flag,
          countryName: "Nepal",
          countryCode: "+977"
        },
        {
          id: 176,
          flagImg: nr_flag,
          countryName: "Nauru",
          countryCode: "+674"
        },
        {
          id: 177,
          flagImg: nu_flag,
          countryName: "Niue",
          countryCode: "+683"
        },
        {
          id: 178,
          flagImg: nz_flag,
          countryName: "New Zealand",
          countryCode: "+64"
        },
        {
          id: 179,
          flagImg: om_flag,
          countryName: "Oman",
          countryCode: "+968"
        },
        {
          id: 180,
          flagImg: pa_flag,
          countryName: "Panama",
          countryCode: "+507"
        },
        {
          id: 181,
          flagImg: pe_flag,
          countryName: "Peru",
          countryCode: "+51"
        },
        {
          id: 182,
          flagImg: pf_flag,
          countryName: "French Polynesia",
          countryCode: "+689"
        },
        {
          id: 183,
          flagImg: pg_flag,
          countryName: "Papua New Guinea",
          countryCode: "+675"
        },
        {
          id: 184,
          flagImg: ph_flag,
          countryName: "Philippines",
          countryCode: "+63"
        },
        {
          id: 185,
          flagImg: pk_flag,
          countryName: "Pakistan",
          countryCode: "+92"
        },
        {
          id: 186,
          flagImg: pl_flag,
          countryName: "Poland",
          countryCode: "+48"
        },
        {
          id: 187,
          flagImg: pm_flag,
          countryName: "Saint Pierre and Miquelon",
          countryCode: "+508"
        },
        {
          id: 188,
          flagImg: pn_flag,
          countryName: "Pitcairn",
          countryCode: "+64"
        },
        {
          id: 189,
          flagImg: pr_flag,
          countryName: "Puerto Rico",
          countryCode: "+1"
        },
        {
          id: 190,
          flagImg: ps_flag,
          countryName: "State of Palestine",
          countryCode: "+970"
        },
        {
          id: 191,
          flagImg: pt_flag,
          countryName: "Portugal",
          countryCode: "+351"
        },
        {
          id: 192,
          flagImg: pw_flag,
          countryName: "Palau",
          countryCode: "+680"
        },
        {
          id: 193,
          flagImg: py_flag,
          countryName: "Paraguay",
          countryCode: "+595"
        },
        {
          id: 194,
          flagImg: qa_flag,
          countryName: "Qatar",
          countryCode: "+974"
        },
        {
          id: 195,
          flagImg: re_flag,
          countryName: "Réunion",
          countryCode: "+262"
        },
        {
          id: 196,
          flagImg: ro_flag,
          countryName: "Romania",
          countryCode: "+40"
        },
        {
          id: 197,
          flagImg: rs_flag,
          countryName: "Serbia",
          countryCode: "+381"
        },
        {
          id: 198,
          flagImg: ru_flag,
          countryName: "Russia",
          countryCode: "+7"
        },
        {
          id: 199,
          flagImg: rw_flag,
          countryName: "Rwanda",
          countryCode: "+250"
        },
        {
          id: 200,
          flagImg: sa_flag,
          countryName: "Saudi Arabia",
          countryCode: "+966"
        },
        {
          id: 201,
          flagImg: sb_flag,
          countryName: "Solomon Islands",
          countryCode: "+677"
        },
        {
          id: 202,
          flagImg: sc_flag,
          countryName: "Seychelles",
          countryCode: "+248"
        },
        {
          id: 203,
          flagImg: sd_flag,
          countryName: "Sudan",
          countryCode: "+249"
        },
        {
          id: 204,
          flagImg: se_flag,
          countryName: "Sweden",
          countryCode: "+46"
        },
        {
          id: 205,
          flagImg: sg_flag,
          countryName: "Singapore",
          countryCode: "+65"
        },
        {
          id: 207,
          flagImg: si_flag,
          countryName: "Slovenia",
          countryCode: "+386"
        },
        {
          id: 208,
          flagImg: sj_flag,
          countryName: "Svalbard and Jan Mayen",
          countryCode: "47"
        },
        {
          id: 209,
          flagImg: sk_flag,
          countryName: "Slovakia",
          countryCode: "+421"
        },
        {
          id: 210,
          flagImg: sl_flag,
          countryName: "Sierra Leone",
          countryCode: "+232"
        },
        {
          id: 212,
          flagImg: sm_flag,
          countryName: "San Marino",
          countryCode: "+378"
        },
        {
          id: 213,
          flagImg: sn_flag,
          countryName: "Senegal",
          countryCode: "+221"
        },
        {
          id: 214,
          flagImg: so_flag,
          countryName: "Somalia",
          countryCode: "+252"
        },
        {
          id: 215,
          flagImg: sr_flag,
          countryName: "Suriname",
          countryCode: "+597"
        },
        {
          id: 216,
          flagImg: ss_flag,
          countryName: "South Sudan",
          countryCode: "+211"
        },
        {
          id: 217,
          flagImg: st_flag,
          countryName: "Sao Tome and Principe",
          countryCode: "+239"
        },
        {
          id: 218,
          flagImg: sv_flag,
          countryName: "El Salvador",
          countryCode: "+503"
        },
        {
          id: 219,
          flagImg: sx_flag,
          countryName: "Sint Maarten",
          countryCode: "+721"
        },
        {
          id: 220,
          flagImg: sy_flag,
          countryName: "Syria",
          countryCode: "+963"
        },
        {
          id: 221,
          flagImg: sz_flag,
          countryName: "Eswatini",
          countryCode: "+268"
        },
        {
          id: 222,
          flagImg: ta_flag,
          countryName: "Tristan da Cunha",
          countryCode: "+290"
        },
        {
          id: 223,
          flagImg: tc_flag,
          countryName: "Turks and Caicos Islands",
          countryCode: "+1"
        },
        {
          id: 224,
          flagImg: td_flag,
          countryName: "Chad",
          countryCode: "+235"
        },
        {
          id: 225,
          flagImg: tg_flag,
          countryName: "Togo",
          countryCode: "+228"
        },
        {
          id: 226,
          flagImg: th_flag,
          countryName: "Thailand",
          countryCode: "+66"
        },
        {
          id: 227,
          flagImg: tj_flag,
          countryName: "Tajikistan",
          countryCode: "+992"
        },
        {
          id: 228,
          flagImg: tk_flag,
          countryName: "Tokelau",
          countryCode: "+690"
        },
        {
          id: 229,
          flagImg: tl_flag,
          countryName: "Timor-Leste",
          countryCode: "+670"
        },
        {
          id: 230,
          flagImg: tm_flag,
          countryName: "Turkmenistan",
          countryCode: "+993"
        },
        {
          id: 231,
          flagImg: tn_flag,
          countryName: "Tunisia",
          countryCode: "+216"
        },
        {
          id: 232,
          flagImg: to_flag,
          countryName: "Tonga",
          countryCode: "+676"
        },
        {
          id: 233,
          flagImg: tr_flag,
          countryName: "Turkey",
          countryCode: "+90"
        },
        {
          id: 234,
          flagImg: tt_flag,
          countryName: "Trinidad and Tobago",
          countryCode: "+1"
        },
        {
          id: 235,
          flagImg: tv_flag,
          countryName: "Tuvalu",
          countryCode: "+688"
        },
        {
          id: 236,
          flagImg: tw_flag,
          countryName: "Taiwan",
          countryCode: "+886"
        },
        {
          id: 237,
          flagImg: tz_flag,
          countryName: "Tanzania",
          countryCode: "+255"
        },
        {
          id: 238,
          flagImg: ua_flag,
          countryName: "Ukraine",
          countryCode: "+380"
        },
        {
          id: 239,
          flagImg: ug_flag,
          countryName: "Uganda",
          countryCode: "+256"
        },
        {
          id: 240,
          flagImg: us_flag,
          countryName: "United States of America",
          countryCode: "+1"
        },
        {
          id: 242,
          flagImg: uy_flag,
          countryName: "Uruguay",
          countryCode: "+598"
        },
        {
          id: 243,
          flagImg: uz_flag,
          countryName: "Uzbekistan",
          countryCode: "+998"
        },
        {
          id: 244,
          flagImg: va_flag,
          countryName: "Holy See",
          countryCode: "+379"
        },
        {
          id: 245,
          flagImg: vc_flag,
          countryName: "Saint Vincent and the Grenadines",
          countryCode: "+1"
        },
        {
          id: 246,
          flagImg: ve_flag,
          countryName: "Venezuela (Bolivarian Republic of)",
          countryCode: "+58"
        },
        {
          id: 247,
          flagImg: vg_flag,
          countryName: "Virgin Islands (British)",
          countryCode: "+1"
        },
        {
          id: 248,
          flagImg: vi_flag,
          countryName: "Virgin Islands (U.S.)",
          countryCode: "+1"
        },
        {
          id: 249,
          flagImg: vn_flag,
          countryName: "Vietnam",
          countryCode: "+84"
        },
        {
          id: 250,
          flagImg: vu_flag,
          countryName: "Vanuatu",
          countryCode: "+678"
        },
        {
          id: 251,
          flagImg: wf_flag,
          countryName: "Wallis and Futuna",
          countryCode: "+681"
        },
        {
          id: 252,
          flagImg: ws_flag,
          countryName: "Samoa",
          countryCode: "+685"
        },
        {
          id: 253,
          flagImg: xk_flag,
          countryName: "Kosovo",
          countryCode: "+383"
        },
        {
          id: 254,
          flagImg: ye_flag,
          countryName: "Yemen",
          countryCode: "+967"
        },
        {
          id: 255,
          flagImg: yt_flag,
          countryName: "Mayotte",
          countryCode: "+262"
        },
        {
          id: 256,
          flagImg: za_flag,
          countryName: "South Africa",
          countryCode: "+27"
        },
        {
          id: 257,
          flagImg: zm_flag,
          countryName: "Zambia",
          countryCode: "+260"
        },
        {
          id: 258,
          flagImg: zw_flag,
          countryName: "Zimbabwe",
          countryCode: "+263"
        }
      ],

      simpleDrop: false,
      simpleDropValue: "",

      inputFlagDrop: false,
      inputFlagDropValue: sampleCountryData,

      simpleMenu: false,
      simpleMenuValue: "",

      inputFlagNumber: false,
      inputFlagNumberValue: sampleCountryData,

      inputFlag: false,
      inputFlagValue: sampleCountryData,
    };
  },
  components: {
    Toggle,
    Layout,
    PageHeader,
  },
  mounted() {
    document.querySelectorAll(".minus").forEach((item) => {
      let productQuantity = item.closest(".input-step").querySelector(".product-quantity");
      item.addEventListener("click", () => {
        if (productQuantity.value > 0) {
          productQuantity.value--;
        }
      });
    });
    document.querySelectorAll(".plus").forEach((item) => {
      let productQuantity = item.closest(".input-step").querySelector(".product-quantity");
      item.addEventListener("click", () => {
        productQuantity.value++;
      });
    });

    document.querySelectorAll(".form-label").forEach((item) => {
      document.addEventListener('click', function (event) {
        if (!item.nextSibling.contains(event.target)) {
          item.closest("div").querySelector(".dropdown-menu").classList.remove("show");
        }
      });
    });
  },
};
</script>

<template>
  <Layout>
    <PageHeader title="Form Advanced" pageTitle="Form" />
    <BRow>
      <BCol lg="12">
        <BCard no-body>
          <BCardHeader>
            <BCardTitle class="mb-0">Custom country select input</BCardTitle>
          </BCardHeader>
          <BCardBody>
            <BRow class="g-3">
              <BCol lg="6">
                <div>
                  <label class="form-label">Simple select example</label>
                  <div data-input-flag data-option-flag-name @click="simpleDrop = !simpleDrop" id="simpleSelect">
                    <input type="text" class="form-control rounded-end flag-input" readonly placeholder="Select country"
                      v-model="simpleDropValue">
                    <div class="dropdown-menu w-100" :class="{ show: simpleDrop }" id="simpleSelectMenu">
                      <ul class="list-unstyled dropdown-menu-list mb-0">
                        <li class="dropdown-item d-flex cursor-pointer" v-for="( data, index ) of countrylist"
                          :key="index" @click="simpleDropValue = data.countryName">
                          <div class="flex-shrink-0 me-2">
                            <img :src="data.flagImg" alt="country flag" class="options-flagimg" height="20">
                          </div>
                          <div class="flex-grow-1">
                            <div class="d-flex">
                              <div class="country-name me-1">{{ data.countryName }}</div>
                              <span class="countrylist-codeno text-muted">{{ data.countryCode
                              }}</span>
                            </div>
                          </div>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="mt-3">
                  <label class="form-label">Select input flag with img & name</label>
                  <div data-input-flag data-option-flag-img-name @click="inputFlagDrop = !inputFlagDrop">
                    <input type="text" class="form-control rounded-end flag-input" readonly placeholder="Select country"
                      :style="{ backgroundImage: `url(${inputFlagDropValue.flagImg})` }"
                      v-model="inputFlagDropValue.countryName">
                    <div class="dropdown-menu w-100" :class="{ show: inputFlagDrop }">
                      <ul class="list-unstyled dropdown-menu-list mb-0">
                        <li class="dropdown-item d-flex cursor-pointer" v-for="( data, index ) of  countrylist "
                          :key="index" @click="inputFlagDropValue = data">
                          <div class="flex-shrink-0 me-2">
                            <img :src="data.flagImg" alt="country flag" class="options-flagimg" height="20">
                          </div>
                          <div class="flex-grow-1">
                            <div class="d-flex">
                              <div class="country-name me-1">{{ data.countryName }}</div>
                              <span class="countrylist-codeno text-muted">{{ data.countryCode
                              }}</span>
                            </div>
                          </div>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div class="mt-3">
                  <label class="form-label">Search input false in dropdown menu</label>
                  <div data-input-flag data-option-flag-name data-search-input="false" @click="simpleMenu = !simpleMenu">
                    <input type="text" class="form-control rounded-end flag-input" readonly placeholder="Select country"
                      v-model="simpleMenuValue">
                    <div class="dropdown-menu w-100" :class="{ show: simpleMenu }">
                      <ul class="list-unstyled dropdown-menu-list mb-0">
                        <li class="dropdown-item d-flex cursor-pointer" v-for="( data, index ) of countrylist"
                          :key="index" @click="simpleMenuValue = data.countryName">
                          <div class="flex-shrink-0 me-2">
                            <img :src="data.flagImg" alt="country flag" class="options-flagimg" height="20">
                          </div>
                          <div class="flex-grow-1">
                            <div class="d-flex">
                              <div class="country-name me-1">{{ data.countryName }}</div>
                              <span class="countrylist-codeno text-muted">{{ data.countryCode
                              }}</span>
                            </div>
                          </div>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </BCol>

              <BCol lg="6">
                <div>
                  <label class="form-label">Select input with buttons & Flag with number</label>
                  <div class="input-group" data-input-flag>
                    <BButton class="btn btn-light border" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                      @click="inputFlagNumber = !inputFlagNumber">
                      <img :src="inputFlagNumberValue.flagImg" alt="flag img" height="20" class="country-flagimg rounded">
                      <span class="ms-2 country-codeno">{{ inputFlagNumberValue.countryCode }}</span>
                    </BButton>
                    <input type="text" class="form-control rounded-end flag-input" value="" placeholder="Enter number"
                      oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" />
                    <div class="dropdown-menu w-100 mt-5" :class="{ show: inputFlagNumber }"
                      @click="inputFlagNumber = !inputFlagNumber">
                      <ul class="list-unstyled dropdown-menu-list mb-0">
                        <li class="dropdown-item d-flex cursor-pointer" v-for="( data, index ) of  countrylist "
                          :key="index" @click="inputFlagNumberValue = data">
                          <div class="flex-shrink-0 me-2">
                            <img :src="data.flagImg" alt="country flag" class="options-flagimg" height="20">
                          </div>
                          <div class="flex-grow-1">
                            <div class="d-flex">
                              <div class="country-name me-1">{{ data.countryName }}</div>
                              <span class="countrylist-codeno text-muted">{{ data.countryCode
                              }}</span>
                            </div>
                          </div>
                        </li>
                      </ul>


                    </div>
                  </div>
                </div>
                <div class="mt-3">
                  <label class="form-label">Select input with buttons & Flag</label>
                  <div class="input-group" data-input-flag data-option-countrycode="false">
                    <BButton class="btn btn-light border" type="button" @click="inputFlag = !inputFlag">
                      <img :src="inputFlagValue.flagImg" alt="flag img" height="20" class="country-flagimg rounded"><span
                        class="ms-2 country-codeno">+ 1</span>
                    </BButton>
                    <input type="text" class="form-control rounded-end flag-input" value="" placeholder="Enter number"
                      oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" />
                    <div class="dropdown-menu w-100 mt-5" :class="{ show: inputFlag }" @click="inputFlag = !inputFlag">
                      <ul class="list-unstyled dropdown-menu-list mb-0 ">
                        <li class="dropdown-item d-flex cursor-pointer" v-for="( data, index ) of  countrylist "
                          :key="index" @click="inputFlagValue = data">
                          <div class="flex-shrink-0 me-2">
                            <img :src="data.flagImg" alt="country flag" class="options-flagimg" height="20">
                          </div>
                          <div class="flex-grow-1">
                            <div class="d-flex">
                              <div class="country-name me-1">{{ data.countryName }}</div>
                              <span class="countrylist-codeno text-muted">{{ data.countryCode
                              }}</span>
                            </div>
                          </div>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </BCol>
            </BRow>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>
    <BRow>
      <BCol lg="12">
        <BCard no-body>
          <BCardHeader>
            <BCardTitle class="mb-0">Form Input Spin</BCardTitle>
          </BCardHeader>

          <BCardBody>
            <div>
              <BRow class="gy-4">
                <BCol sm="6">
                  <div>
                    <h5 class="fs-13 fw-medium text-muted">Default</h5>

                    <div class="input-step">
                      <BButton type="button" class="minus">–</BButton>
                      <input type="number" class="product-quantity" value="2" min="0" max="100" readonly />
                      <BButton type="button" class="plus">+</BButton>
                    </div>
                  </div>
                </BCol>

                <BCol sm="6">
                  <div>
                    <h5 class="fs-13 fw-medium text-muted">Light</h5>
                    <div class="input-step light">
                      <BButton type="button" class="minus">–</BButton>
                      <input type="number" class="product-quantity" value="5" min="0" max="100" readonly />
                      <BButton type="button" class="plus">+</BButton>
                    </div>
                  </div>
                </BCol>
              </BRow>

              <div class="mt-4 pt-2">
                <BRow class="gy-4">
                  <BCol sm="6">
                    <div>
                      <h5 class="fs-13 fw-medium text-muted">
                        Default (Full width)
                      </h5>
                      <div class="input-step full-width">
                        <BButton type="button" class="minus">–</BButton>
                        <input type="number" class="product-quantity" value="4" min="0" max="100" readonly />
                        <BButton type="button" class="plus">+</BButton>
                      </div>
                    </div>
                  </BCol>

                  <BCol sm="6">
                    <div>
                      <h5 class="fs-13 fw-medium text-muted">
                        Light (Full width)
                      </h5>
                      <div class="input-step full-width light">
                        <BButton type="button" class="minus">–</BButton>
                        <input type="number" class="product-quantity" value="6" min="0" max="100" readonly />
                        <BButton type="button" class="plus">+</BButton>
                      </div>
                    </div>
                  </BCol>
                </BRow>
              </div>

              <BRow class="mt-4 pt-2">
                <h5 class="fs-13 fw-medium text-muted">Colored</h5>
                <div class="d-flex flex-wrap align-items-start gap-2">
                  <div class="input-step step-primary">
                    <button type="button" class="minus">–</button>
                    <input type="number" class="product-quantity" value="6" min="0" max="100" readonly />
                    <button type="button" class="plus">+</button>
                  </div>
                  <div class="input-step step-secondary">
                    <button type="button" class="minus">–</button>
                    <input type="number" class="product-quantity" value="1" min="0" max="100" readonly />
                    <button type="button" class="plus">+</button>
                  </div>
                  <div class="input-step step-success">
                    <button type="button" class="minus">–</button>
                    <input type="number" class="product-quantity" value="3" min="0" max="100" readonly />
                    <button type="button" class="plus">+</button>
                  </div>
                  <div class="input-step step-info">
                    <button type="button" class="minus">–</button>
                    <input type="number" class="product-quantity" value="1" min="0" max="100" readonly />
                    <button type="button" class="plus">+</button>
                  </div>
                  <div class="input-step step-warning">
                    <button type="button" class="minus">–</button>
                    <input type="number" class="product-quantity" value="4" min="0" max="100" readonly />
                    <button type="button" class="plus">+</button>
                  </div>
                  <div class="input-step step-danger">
                    <button type="button" class="minus">–</button>
                    <input type="number" class="product-quantity" value="5" min="0" max="100" readonly />
                    <button type="button" class="plus">+</button>
                  </div>
                </div>
              </BRow>
            </div>
          </BCardBody>
        </BCard>
      </BCol>
    </BRow>

    <BRow>
      <BCol lg="6">
        <BCard no-body>
          <BCardBody>
            <BCardTitle>Bootstrap MaxLength</BCardTitle>

            <label class="mb-1 fw-medium">Default usage</label>
            <p class="text-muted font-13">
              The badge will show up by default when the remaining chars are 10
              or less:
            </p>
            <input v-model="maxlen" type="text" class="form-control" :maxlength="10" />
            <div v-if="maxlen" class="text-center">
              <p class="badge position-absolute" :class="{
                'bg-success': maxlen.length !== 10,
                'bg-danger': maxlen.length === 10,
              }">
                {{ maxlen.length }} / 10
              </p>
            </div>

            <div>
              <label class="mb-1 mt-3 fw-medium">Threshold value</label>
              <p class="text-muted font-13">
                Do you want the badge to show up when there are 20 chars or
                less? Use the
                <code>threshold</code>
                option:
              </p>
              <input v-model="data" type="text" :maxlength="25" class="form-control" />
              <div class="text-center">
                <p v-if="data" class="badge position-absolute" :class="{
                  'bg-success': data.length !== 25,
                  'bg-danger': data.length === 25,
                }">
                  {{ data.length }} / 25
                </p>
              </div>
            </div>

            <div>
              <label class="mb-1 mt-3 fw-medium">All the options</label>
              <p class="text-muted font-13">
                Please note: if the
                <code>alwaysShow</code> option is enabled, the
                <code>threshold</code> option is ignored.
              </p>
              <input v-model="option" type="text" class="form-control" :maxlength="25" />
              <div class="text-center">
                <p v-if="option" class="badge position-absolute" :class="{
                  'bg-success': option.length !== 25,
                  'bg-danger': option.length === 25,
                }">
                  You typed
                  {{ option.length }} out of 25 chars available.
                </p>
              </div>
            </div>

            <div>
              <label class="mb-1 mt-3 fw-medium">Textarea</label>
              <p class="text-muted font-13">
                Bootstrap maxlength supports textarea as well as inputs. Even on
                old IE.
              </p>
              <textarea v-model="textarea" class="form-control" :maxlength="225" rows="3"
                placeholder="This textarea has a limit of 225 chars."></textarea>
              <div class="text-center">
                <p v-if="textarea" class="badge position-absolute" :class="{
                  'bg-success': textarea.length !== 225,
                  'bg-danger': textarea.length === 225,
                }">
                  {{ textarea.length }} / 225
                </p>
              </div>
            </div>
          </BCardBody>
        </BCard>
      </BCol>
      <BCol lg="6">
        <BCard no-body>
          <BCardHeader>
            <BCardTitle class="mb-1">Switch</BCardTitle>

            <p class="text-muted font-13">Use <code>@vueform/toggle</code> for below switches.</p>

            <BCardBody>
              <Toggle v-model="value" class="toggle-red me-2" />
              <Toggle v-model="value1" class="toggle-blue me-2" />
              <Toggle v-model="value2" class="toggle-primary me-2" />
              <Toggle v-model="value3" class="toggle-warning me-2" />
              <Toggle v-model="value4" class="toggle-info" />
            </BCardBody>
          </BCardHeader>
        </BCard>
      </BCol>
    </BRow>
  </Layout>
</template>
