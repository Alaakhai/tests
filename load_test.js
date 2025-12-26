import http from 'k6/http';
import { check } from 'k6';

export let options = {
  stages: [
    { duration: '30s', target: 150 },  // تصعيد عدد المستخدمين حتى 150 خلال 30 ثانية
    { duration: '1m', target: 150 },   // تشغيل النظام بثبات دقيقة كاملة
    { duration: '20s', target: 0 },    // تقليل الحمل تدريجيًا
  ],
};

export default function () {
  let res = http.post('http://localhost:8000/api/attendance/verify-face', JSON.stringify({
    image: "Base64SampleString",
    schedule_id: 1
  }), {
    headers: { 'Content-Type': 'application/json' },
  });

  check(res, {
    'status is 200': (r) => r.status === 200,
  });
}
