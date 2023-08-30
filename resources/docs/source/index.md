---
title: API Reference

language_tabs:
    - bash
    - javascript

includes:

search: true

toc_footers:
    - <a href='http://github.com/mpociot/documentarian'>Documentation Powered by Documentarian</a>
---

<!-- START_INFO -->

# Info

Welcome to the generated API reference.
[Get Postman Collection](http://localhost/docs/collection.json)

<!-- END_INFO -->

#Admin/Báo cáo sự cố

APIs Báo cáo sự cố

<!-- START_d18c00cec204090e0ad4ac9bd6d93a9b -->

## Danh sách báo cáo sự cố

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/report_problem" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"date_from":"aspernatur","date_to":"occaecati"}'

```

```javascript
const url = new URL("http://localhost/api/admin/report_problem");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    date_from: "aspernatur",
    date_to: "occaecati",
};

fetch(url, {
    method: "GET",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/admin/report_problem`

#### Body Parameters

| Parameter   | Type | Status   | Description   |
| ----------- | ---- | -------- | ------------- |
| `date_from` | date | optional | ngày bắt đầu  |
| `date_to`   | date | optional | ngày kết thúc |

<!-- END_d18c00cec204090e0ad4ac9bd6d93a9b -->

#Admin/Quản lý/Bài đăng tìm phòng trọ

<!-- START_c0cd03c9344cba201d845abd05dc9bde -->

## Danh cách phòng đăng tìm phòng trọ

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/mo_posts" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/admin/mo_posts");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/admin/mo_posts`

<!-- END_c0cd03c9344cba201d845abd05dc9bde -->

<!-- START_39eb428b672fb9f848c173c72022c823 -->

## Thong tin bài đăng tìm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/mo_posts/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/admin/mo_posts/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/admin/mo_posts/{post_id}`

<!-- END_39eb428b672fb9f848c173c72022c823 -->

<!-- START_77f35482820cc709daaed6a5cda658b3 -->

## Xóa 1 phòng trọ

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/admin/mo_posts/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/admin/mo_posts/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`DELETE api/admin/mo_posts/{post_id}`

#### URL Parameters

| Parameter    | Status   | Description |
| ------------ | -------- | ----------- |
| `store_code` | required | Store code. |

<!-- END_77f35482820cc709daaed6a5cda658b3 -->

#Admin/Quản lý/Dịch vụ bán

<!-- START_efc727b8f48d21da0b2b3dcfe1c0dfc7 -->

## Danh sách dịch vụ bán

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/service_sells" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/admin/service_sells");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/admin/service_sells`

<!-- END_efc727b8f48d21da0b2b3dcfe1c0dfc7 -->

<!-- START_8f4e6c61683b0c171c8c70ce61d5cfbd -->

## Thong tin 1 dịch vụ

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/service_sells/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/admin/service_sells/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/admin/service_sells/{service_sell_id}`

<!-- END_8f4e6c61683b0c171c8c70ce61d5cfbd -->

<!-- START_8a4b325eb355744e8b831d10a1cdbf1d -->

## Xóa 1 dịch vụ

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/admin/service_sells/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/admin/service_sells/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`DELETE api/admin/service_sells/{service_sell_id}`

<!-- END_8a4b325eb355744e8b831d10a1cdbf1d -->

<!-- START_de2f5c9aa0d423020f7f02629f2c8b69 -->

## Thêm dịch vụ bán

> Example request:

```bash
curl -X POST \
    "http://localhost/api/admin/service_sells" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/admin/service_sells");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/admin/service_sells`

<!-- END_de2f5c9aa0d423020f7f02629f2c8b69 -->

<!-- START_b0c2805776897e60c93addd61a8ff676 -->

## Cập nhật 1 dịch vụ

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/admin/service_sells/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/admin/service_sells/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`PUT api/admin/service_sells/{service_sell_id}`

<!-- END_b0c2805776897e60c93addd61a8ff676 -->

#Admin/Quản lý/Phòng trọ

<!-- START_1c8ea068c276db6e0c59ecce3c932292 -->

## Danh cách phòng trọ

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/motels" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/admin/motels");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/admin/motels`

<!-- END_1c8ea068c276db6e0c59ecce3c932292 -->

<!-- START_ce5e22cbb12b347c9dbc404d1e9d392e -->

## Thong tin 1 phòng trọ

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/motels/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/admin/motels/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/admin/motels/{motel_id}`

<!-- END_ce5e22cbb12b347c9dbc404d1e9d392e -->

<!-- START_4ffde64d8438d5d28b980ba5eefc1682 -->

## Xóa 1 phòng trọ

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/admin/motels/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/admin/motels/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`DELETE api/admin/motels/{motel_id}`

#### URL Parameters

| Parameter    | Status   | Description |
| ------------ | -------- | ----------- |
| `store_code` | required | Store code. |

<!-- END_4ffde64d8438d5d28b980ba5eefc1682 -->

#Admin/Quản lý/User

<!-- START_1fdf5c126c9b5b722e5044c3f680bf8e -->

## Danh sách user

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/users" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"omnis","number_phone":"et","email":"dolorem","date_from":"recusandae","date_to":"voluptates","descending":true,"sort_by":"maxime","limit":11}'

```

```javascript
const url = new URL("http://localhost/api/admin/users");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    name: "omnis",
    number_phone: "et",
    email: "dolorem",
    date_from: "recusandae",
    date_to: "voluptates",
    descending: true,
    sort_by: "maxime",
    limit: 11,
};

fetch(url, {
    method: "GET",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/admin/users`

#### Body Parameters

| Parameter      | Type     | Status   | Description                         |
| -------------- | -------- | -------- | ----------------------------------- |
| `name`         | string   | optional | tên người dùng                      |
| `number_phone` | số       | optional | điện thoại người dùng               |
| `email`        | string   | optional | email                               |
| `date_from`    | datetime | optional | ngày bắt đầu                        |
| `date_to`      | datetime | optional | ngày kết thúc                       |
| `descending`   | boolean  | optional | sắp xếp theo (default true)         |
| `sort_by`      | string   | optional | sắp xếp theo tên cột (ranked, name) |
| `limit`        | integer  | optional | Số lượng bản ghi sẽ lấy             |

<!-- END_1fdf5c126c9b5b722e5044c3f680bf8e -->

<!-- START_9f504ae803f85a861c00cbe41c44a42f -->

## Thong tin 1 user

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/admin/users/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/admin/users/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/admin/users/{user_id}`

<!-- END_9f504ae803f85a861c00cbe41c44a42f -->

<!-- START_9edb91caa7d4532e0d55bcd1887abf3a -->

## Cấp chủ nhà

> Example request:

```bash
curl -X POST \
    "http://localhost/api/admin/users/1/set_host" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/admin/users/1/set_host");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/admin/users/{user_id}/set_host`

<!-- END_9edb91caa7d4532e0d55bcd1887abf3a -->

#Bill/Hóa đơn (1 phòng)

<!-- START_90e06e6a0556dcc207be65f574c5c424 -->

## Thêm 1 hóa đơn

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/manage/bills" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"contract_id":16,"date_payment":"ex","total_money_motel":295,"total_money_service":6847.739913,"discount":16.2727,"deposit_money":1953.87,"services":[],"total_final":5457.97553}'

```

```javascript
const url = new URL("http://localhost/api/user/manage/bills");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    contract_id: 16,
    date_payment: "ex",
    total_money_motel: 295,
    total_money_service: 6847.739913,
    discount: 16.2727,
    deposit_money: 1953.87,
    services: [],
    total_final: 5457.97553,
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/manage/bills`

#### Body Parameters

| Parameter             | Type     | Status   | Description                                                                                                                       |
| --------------------- | -------- | -------- | --------------------------------------------------------------------------------------------------------------------------------- |
| `contract_id`         | integer  | optional | require mã hợp đồng                                                                                                               |
| `date_payment`        | datetime | optional | ngày thanh toán                                                                                                                   |
| `total_money_motel`   | float    | optional | tổng tiền phòng                                                                                                                   |
| `total_money_service` | float    | optional | tổng tiền dịch vụ                                                                                                                 |
| `discount`            | float    | optional | giảm giá tiền phòng                                                                                                               |
| `deposit_money`       | float    | optional | tiền cọc                                                                                                                          |
| `services`            | array    | optional | listServiceClose [{"id": "45","service_unit": "kwh","quantity": "2050"},{"id": "47","service_unit": "every_use","quantity": "3"}] |
| `total_final`         | float    | optional | (total_money_motel + total_money_service) - discount                                                                              |

<!-- END_90e06e6a0556dcc207be65f574c5c424 -->

<!-- START_ac9b921c173e3be521c47c39f7bd5ac6 -->

## api/user/manage/bills

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/manage/bills" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/bills");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/manage/bills`

<!-- END_ac9b921c173e3be521c47c39f7bd5ac6 -->

<!-- START_a0f2a6e92fabc3e603e0150373058648 -->

## Lấy 1 hóa đơn

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/manage/bills/qui" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/bills/qui");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/manage/bills/{bill_id}`

#### URL Parameters

| Parameter | Status   | Description             |
| --------- | -------- | ----------------------- |
| `bill_id` | optional | int tìm hóa đơn theo mã |

<!-- END_a0f2a6e92fabc3e603e0150373058648 -->

<!-- START_130e9e11d9884217db49f6577e6777c4 -->

## Sửa 1 hóa đơn

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/user/manage/bills/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"service_close_id":10,"date_payment":"molestias","total_money_motel":4.09736,"total_money_service":0,"discount":117.238,"services":[],"total_final":2820.420775274}'

```

```javascript
const url = new URL("http://localhost/api/user/manage/bills/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    service_close_id: 10,
    date_payment: "molestias",
    total_money_motel: 4.09736,
    total_money_service: 0,
    discount: 117.238,
    services: [],
    total_final: 2820.420775274,
};

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`PUT api/user/manage/bills/{bill_id}`

#### Body Parameters

| Parameter             | Type     | Status   | Description                                                                                                                       |
| --------------------- | -------- | -------- | --------------------------------------------------------------------------------------------------------------------------------- |
| `service_close_id`    | integer  | optional | require mã mã dịch vụ chốt gần nhất                                                                                               |
| `date_payment`        | datetime | optional | ngày thanh toán                                                                                                                   |
| `total_money_motel`   | float    | optional | tổng tiền phòng                                                                                                                   |
| `total_money_service` | float    | optional | tổng tiền dịch vụ                                                                                                                 |
| `discount`            | float    | optional | giảm giá tiền phòng                                                                                                               |
| `services`            | array    | optional | listServiceClose [{"id": "45","service_unit": "kwh","quantity": "2050"},{"id": "47","service_unit": "every_use","quantity": "3"}] |
| `total_final`         | float    | optional | (total_money_motel + total_money_service) - discount                                                                              |

<!-- END_130e9e11d9884217db49f6577e6777c4 -->

#Nơi chốn

<!-- START_254a33c50b7024631f5710c66c10a4ec -->

## Lấy danh sách vùng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/place/vn/ut/reprehenderit" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/place/vn/ut/reprehenderit");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (200):

```json
{
    "code": 200,
    "success": true,
    "data": [],
    "msg_code": "SUCCESS",
    "msg": "THÀNH CÔNG"
}
```

### HTTP Request

`GET api/place/vn/{type}/{parent_id}`

#### URL Parameters

| Parameter   | Status   | Description                                 |
| ----------- | -------- | ------------------------------------------- | -------------------- | ----------------- |
| `type`      | required | mục cần lấy ( province(tỉnh,thành phố)      | district(quận,huyện) | wards(phường,xã)) |
| `parent_id` | required | id mục cha, riêng province có thể không cần |

<!-- END_254a33c50b7024631f5710c66c10a4ec -->

<!-- START_771993d39791d36e85d775e76660728d -->

## Lấy danh sách vùng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/place/vn/sed" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/place/vn/sed");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (200):

```json
{
    "code": 200,
    "success": true,
    "data": [],
    "msg_code": "SUCCESS",
    "msg": "THÀNH CÔNG"
}
```

### HTTP Request

`GET api/place/vn/{type}`

#### URL Parameters

| Parameter   | Status   | Description                                 |
| ----------- | -------- | ------------------------------------------- | -------------------- | ----------------- |
| `type`      | required | mục cần lấy ( province(tỉnh,thành phố)      | district(quận,huyện) | wards(phường,xã)) |
| `parent_id` | required | id mục cha, riêng province có thể không cần |

<!-- END_771993d39791d36e85d775e76660728d -->

#Upload video

<!-- START_aea3a53954263cd6388af3351ee4fa9f -->

## Upload 1 video

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/videos" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"video":"quod"}'

```

```javascript
const url = new URL("http://localhost/api/user/videos");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    video: "quod",
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/videos`

#### Body Parameters

| Parameter | Type | Status   | Description |
| --------- | ---- | -------- | ----------- |
| `video`   | file | required | File video  |

<!-- END_aea3a53954263cd6388af3351ee4fa9f -->

#Upload ảnh

<!-- START_26d19727c6a2be6eb1ed578a9c911e25 -->

## Upload 1 ảnh

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/images" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"image":"quia"}'

```

```javascript
const url = new URL("http://localhost/api/user/images");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    image: "quia",
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/images`

#### Body Parameters

| Parameter | Type | Status   | Description |
| --------- | ---- | -------- | ----------- |
| `image`   | file | required | File ảnh    |

<!-- END_26d19727c6a2be6eb1ed578a9c911e25 -->

#User/Chat

<!-- START_88694a70011fd2e6f7f9a2fb51cf774f -->

## Danh sách người chat với user

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/community/person_chat" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/community/person_chat");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/community/person_chat`

<!-- END_88694a70011fd2e6f7f9a2fb51cf774f -->

<!-- START_77b6d09a86ee8a5750832faf6569d376 -->

## Danh sách tin nhắn với 1 người

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/community/person_chat/1/messages" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"content":"incidunt","images":"porro"}'

```

```javascript
const url = new URL(
    "http://localhost/api/user/community/person_chat/1/messages"
);

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    content: "incidunt",
    images: "porro",
};

fetch(url, {
    method: "GET",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/community/person_chat/{to_user_id}/messages`

#### Body Parameters

| Parameter | Type     | Status   | Description                                    |
| --------- | -------- | -------- | ---------------------------------------------- |
| `content` | required | optional | Nội dung                                       |
| `images`  | required | optional | List danh sách ảnh sp (VD: ["linl1", "link2"]) |

<!-- END_77b6d09a86ee8a5750832faf6569d376 -->

<!-- START_6765f2b2027516c7efcdda541a5e2abd -->

## Gửi tin nhắn

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/community/person_chat/1/messages" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"content":"nihil","images":"natus"}'

```

```javascript
const url = new URL(
    "http://localhost/api/user/community/person_chat/1/messages"
);

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    content: "nihil",
    images: "natus",
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/community/person_chat/{to_user_id}/messages`

#### Body Parameters

| Parameter | Type     | Status   | Description                                    |
| --------- | -------- | -------- | ---------------------------------------------- |
| `content` | required | optional | Nội dung                                       |
| `images`  | required | optional | List danh sách ảnh sp (VD: ["linl1", "link2"]) |

<!-- END_6765f2b2027516c7efcdda541a5e2abd -->

#User/Chỉ số

<!-- START_9525acf6d3c4eff58e4dcb4cb45544e3 -->

## Lấy tất cả chỉ số đếm

Khách hàng chat cho user
Nhận badges realtime
var socket = io("http://localhost:6441")
socket.on("badges:badges_user:1", function(data) {
console.log(data)
})
1 là user_id

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/badges" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/badges");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/badges`

<!-- END_9525acf6d3c4eff58e4dcb4cb45544e3 -->

<!-- START_45b398020eacfe7b6c5a23bc4b4ed6bc -->

## chỉ số về motel

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/summary_motel" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/summary_motel");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/summary_motel`

<!-- END_45b398020eacfe7b6c5a23bc4b4ed6bc -->

#User/Cộng đồng/Báo cáo Sự cố

<!-- START_bf8b8b8cd748bee8e222d2b4f572ec95 -->

## Tạo 1 báo cáo sự cố

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/community/report_problem" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"user_id":"neque","motel_id":"quia","reason":"hic","describe_problem":"laboriosam","status":11,"severity":3}'

```

```javascript
const url = new URL("http://localhost/api/user/community/report_problem");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    user_id: "neque",
    motel_id: "quia",
    reason: "hic",
    describe_problem: "laboriosam",
    status: 11,
    severity: 3,
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/community/report_problem`

#### Body Parameters

| Parameter          | Type    | Status   | Description                                                         |
| ------------------ | ------- | -------- | ------------------------------------------------------------------- |
| `user_id`          | string  | optional | Tên                                                                 |
| `motel_id`         | string  | optional | Tên                                                                 |
| `reason`           | string  | optional | Tên                                                                 |
| `describe_problem` | string  | optional | Tên                                                                 |
| `status`           | integer | optional | Trạng thái báo cáo [0: Đang tiến hành, 1: Đã hủy, 2: Đã hoàn thành] |
| `severity`         | integer | optional | Mức độ nghiêm trọng [0: Thấp 1: Bình thường, 2: Cao ]               |

<!-- END_bf8b8b8cd748bee8e222d2b4f572ec95 -->

<!-- START_0bb61a4b956a78a123ea715a9ce30cc7 -->

## Danh sách báo cáo sự cố

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/community/report_problem" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"date_from":"et","date_to":"vitae"}'

```

```javascript
const url = new URL("http://localhost/api/user/community/report_problem");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    date_from: "et",
    date_to: "vitae",
};

fetch(url, {
    method: "GET",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/community/report_problem`

#### Body Parameters

| Parameter   | Type | Status   | Description   |
| ----------- | ---- | -------- | ------------- |
| `date_from` | date | optional | ngày bắt đầu  |
| `date_to`   | date | optional | ngày kết thúc |

<!-- END_0bb61a4b956a78a123ea715a9ce30cc7 -->

<!-- START_177d6006e83d6c24c9d3ae6af6b4ff48 -->

## Lấy 1 báo cáo sự cố

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/community/report_problem/velit" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/community/report_problem/velit");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/community/report_problem/{report_problem_id}`

#### URL Parameters

| Parameter           | Status   | Description          |
| ------------------- | -------- | -------------------- |
| `report_problem_id` | optional | int Mã báo cáo sự cố |

<!-- END_177d6006e83d6c24c9d3ae6af6b4ff48 -->

<!-- START_c8bc7d9530449e81243bc9b903106e73 -->

## Xóa 1 báo cáo sự cố

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/user/community/report_problem/commodi" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL(
    "http://localhost/api/user/community/report_problem/commodi"
);

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`DELETE api/user/community/report_problem/{report_problem_id}`

#### URL Parameters

| Parameter           | Status   | Description          |
| ------------------- | -------- | -------------------- |
| `report_problem_id` | optional | int Mã báo cáo sự cố |

<!-- END_c8bc7d9530449e81243bc9b903106e73 -->

<!-- START_99c1b7b8b67176e618ca8eb65103d426 -->

## Cập nhật 1 báo cáo sự cố

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/user/community/report_problem/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"reason":"fugit","describe_problem":"eos","status":13}'

```

```javascript
const url = new URL("http://localhost/api/user/community/report_problem/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    reason: "fugit",
    describe_problem: "eos",
    status: 13,
};

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`PUT api/user/community/report_problem/{report_problem_id}`

#### Body Parameters

| Parameter          | Type    | Status   | Description                                                         |
| ------------------ | ------- | -------- | ------------------------------------------------------------------- |
| `reason`           | string  | optional | Tên                                                                 |
| `describe_problem` | string  | optional | Tên                                                                 |
| `status`           | integer | optional | Trạng thái báo cáo [0: Đang tiến hành, 1: Đã hủy, 2: Đã hoàn thành] |

<!-- END_99c1b7b8b67176e618ca8eb65103d426 -->

#User/Cộng đồng/Cộng đồng tìm phòng

<!-- START_f70fbef51882ab6f2fabfff8012de6ba -->

## Danh cách phòng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/community/mo_posts?title=quo&money_from=aperiam&money_to=architecto&province=ut&district=optio&wards=tenetur&sex=est&has=omnis&sort_by=qui&descending=sit" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/community/mo_posts");

let params = {
    title: "quo",
    money_from: "aperiam",
    money_to: "architecto",
    province: "ut",
    district: "optio",
    wards: "tenetur",
    sex: "est",
    has: "omnis",
    sort_by: "qui",
    descending: "sit",
};
Object.keys(params).forEach((key) => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (200):

```json
{
    "code": 200,
    "success": true,
    "msg_code": "SUCCESS",
    "msg": "THÀNH CÔNG",
    "data": {
        "current_page": 1,
        "data": [],
        "first_page_url": "http://localhost/api/user/community/mo_posts?page=1",
        "from": null,
        "last_page": 1,
        "last_page_url": "http://localhost/api/user/community/mo_posts?page=1",
        "links": [
            {
                "url": null,
                "label": "&laquo; Previous",
                "active": false
            },
            {
                "url": "http://localhost/api/user/community/mo_posts?page=1",
                "label": "1",
                "active": true
            },
            {
                "url": null,
                "label": "Next &raquo;",
                "active": false
            }
        ],
        "next_page_url": null,
        "path": "http://localhost/api/user/community/mo_posts",
        "per_page": 20,
        "prev_page_url": null,
        "to": null,
        "total": 0
    }
}
```

### HTTP Request

`GET api/user/community/mo_posts`

#### Query Parameters

| Parameter    | Status   | Description                       |
| ------------ | -------- | --------------------------------- |
| `title`      | optional | tìm theo tiêu đề                  |
| `money_from` | optional | tiền tối thiểu                    |
| `money_to`   | optional | tiền tối đa                       |
| `province`   | optional | tỉnh                              |
| `district`   | optional | quận                              |
| `wards`      | optional | huyện                             |
| `sex`        | optional | giới tính                         |
| `has`        | optional | boolean tất cả field có kiểu bool |
| `sort_by`    | optional | string tên column                 |
| `descending` | optional | boolean                           |

<!-- END_f70fbef51882ab6f2fabfff8012de6ba -->

<!-- START_bde3666c63e360115e3968b730ac8963 -->

## Thông tin 1 phòng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/community/mo_posts/1?id=perspiciatis" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/community/mo_posts/1");

let params = {
    id: "perspiciatis",
};
Object.keys(params).forEach((key) => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (200):

```json
{
    "code": 200,
    "success": true,
    "msg_code": "SUCCESS",
    "msg": "THÀNH CÔNG",
    "data": {
        "id": 1,
        "user_id": 2,
        "motel_id": "2",
        "phone_number": "0868914789",
        "title": "tiêu đè",
        "description": "Mô tả",
        "motel_name": 44,
        "capacity": 1,
        "sex": 1,
        "area": 25,
        "money": 10000,
        "deposit": 10000,
        "electric_money": 1000,
        "water_money": 10000,
        "has_wifi": true,
        "wifi_money": 10000,
        "has_park": true,
        "park_money": 1000,
        "province_name": null,
        "district_name": null,
        "wards_name": null,
        "province": 1,
        "district": 1,
        "wards": 1,
        "address_detail": "xxx",
        "has_wc": true,
        "has_window": true,
        "has_security": true,
        "has_free_move": true,
        "has_own_owner": true,
        "has_air_conditioner": true,
        "has_water_heater": true,
        "has_kitchen": true,
        "has_fridge": true,
        "has_washing_machine": true,
        "has_mezzanine": true,
        "has_bed": true,
        "has_wardrobe": true,
        "has_tivi": true,
        "has_pet": true,
        "has_balcony": true,
        "hour_open": 1,
        "minute_open": 1,
        "hour_close": 1,
        "minute_close": 1,
        "created_at": "2022-07-30T04:30:24.000000Z",
        "updated_at": "2022-07-30T04:30:24.000000Z",
        "is_favorite": false
    }
}
```

### HTTP Request

`GET api/user/community/mo_posts/{mo_post_id}`

#### Query Parameters

| Parameter | Status   | Description   |
| --------- | -------- | ------------- |
| `id`      | optional | phòng cần xem |

<!-- END_bde3666c63e360115e3968b730ac8963 -->

#User/Cộng đồng/Giỏ hàng

<!-- START_886bdc924bd992eb2ac818c9fa2e85d4 -->

## Thêm sp vào giỏ

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/community/cart_service_sells" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"service_sell_id":7,"quantity":12}'

```

```javascript
const url = new URL("http://localhost/api/user/community/cart_service_sells");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    service_sell_id: 7,
    quantity: 12,
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/community/cart_service_sells`

#### Body Parameters

| Parameter         | Type    | Status   | Description |
| ----------------- | ------- | -------- | ----------- |
| `service_sell_id` | integer | optional | id dịch vụ  |
| `quantity`        | integer | optional | số lượng    |

<!-- END_886bdc924bd992eb2ac818c9fa2e85d4 -->

<!-- START_6c1f5cf3b54868c466c70c992f8f6a46 -->

## Danh sách sản phẩm trong giỏ hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/community/cart_service_sells" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/community/cart_service_sells");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/community/cart_service_sells`

<!-- END_6c1f5cf3b54868c466c70c992f8f6a46 -->

<!-- START_9fd729074f4d8bb2cdde43f934af9afa -->

## Xóa sp khỏi giỏ

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/user/community/cart_service_sells/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"cart_id":10}'

```

```javascript
const url = new URL("http://localhost/api/user/community/cart_service_sells/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    cart_id: 10,
};

fetch(url, {
    method: "DELETE",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`DELETE api/user/community/cart_service_sells/{cart_id}`

#### Body Parameters

| Parameter | Type    | Status   | Description |
| --------- | ------- | -------- | ----------- |
| `cart_id` | integer | optional | cart_id     |

<!-- END_9fd729074f4d8bb2cdde43f934af9afa -->

<!-- START_fbb322c0d1fa2d8f9d65f302f814ebb6 -->

## Cập nhật số lượng

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/user/community/cart_service_sells/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"cart_id":6,"quantity":15}'

```

```javascript
const url = new URL("http://localhost/api/user/community/cart_service_sells/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    cart_id: 6,
    quantity: 15,
};

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`PUT api/user/community/cart_service_sells/{cart_id}`

#### Body Parameters

| Parameter  | Type    | Status   | Description |
| ---------- | ------- | -------- | ----------- |
| `cart_id`  | integer | optional | cart_id     |
| `quantity` | integer | optional | số lượng    |

<!-- END_fbb322c0d1fa2d8f9d65f302f814ebb6 -->

#User/Cộng đồng/Hợp đồng

<!-- START_fb2e9d1c73124e148afbd2aae14837b7 -->

## Thêm 1 hợp đồng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/community/contracts" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"rental_agent":"at","motel_id":17,"money":0.16,"rent_from":"et","rent_to":"totam","pay_start":"soluta","payment_space":18,"deposit_money":62262.21058,"list_renter":"ipsum"}'

```

```javascript
const url = new URL("http://localhost/api/user/community/contracts");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    rental_agent: "at",
    motel_id: 17,
    money: 0.16,
    rent_from: "et",
    rent_to: "totam",
    pay_start: "soluta",
    payment_space: 18,
    deposit_money: 62262.21058,
    list_renter: "ipsum",
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/community/contracts`

#### Body Parameters

| Parameter       | Type    | Status   | Description                      |
| --------------- | ------- | -------- | -------------------------------- |
| `rental_agent`  | string  | optional | tên người đại diện               |
| `motel_id`      | integer | optional | phòng cho thuê                   |
| `money`         | float   | optional | tiền phòng                       |
| `rent_from`     | time    | optional | thuê từ ngày                     |
| `rent_to`       | time    | optional | thuê đến ngày                    |
| `pay_start`     | time    | optional | ngày bắt đầu tính tiền           |
| `payment_space` | integer | optional | kỳ thanh toán                    |
| `deposit_money` | float   | optional | tiền đặt cọc                     |
| `list_renter`   | list    | optional | danh sách người thuê [renter_id] |

<!-- END_fb2e9d1c73124e148afbd2aae14837b7 -->

<!-- START_4319e3f8933cf196ab8991c74cd1af88 -->

## Danh cách hợp đồng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/community/contracts?contract_status=veniam" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/community/contracts");

let params = {
    contract_status: "veniam",
};
Object.keys(params).forEach((key) => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/community/contracts`

#### Query Parameters

| Parameter         | Status   | Description                                                                       |
| ----------------- | -------- | --------------------------------------------------------------------------------- |
| `contract_status` | optional | int (2 đang hoạt động,1 quá hạn, 0 đã thanh lý,3 sắp hết hạn trong vòng 1 tháng ) |

<!-- END_4319e3f8933cf196ab8991c74cd1af88 -->

<!-- START_5ddd25cce4b427d2446dec5d33034ea4 -->

## Thong tin 1 hợp đồng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/community/contracts/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/community/contracts/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/community/contracts/{contract_id}`

<!-- END_5ddd25cce4b427d2446dec5d33034ea4 -->

<!-- START_a7c1f3edf59ad3f082bb944b93813331 -->

## Xóa 1 hợp đồng

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/user/community/contracts/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/community/contracts/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`DELETE api/user/community/contracts/{contract_id}`

<!-- END_a7c1f3edf59ad3f082bb944b93813331 -->

<!-- START_cab07f2aba66705595db6977ab8e570e -->

## Cập nhật 1 hợp đồng

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/user/community/contracts/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"rental_agent":"ea","motel_id":1,"money":1751561.831651,"rent_from":"iste","rent_to":"magni","pay_start":"unde","payment_space":8,"deposit_money":10737.53354}'

```

```javascript
const url = new URL("http://localhost/api/user/community/contracts/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    rental_agent: "ea",
    motel_id: 1,
    money: 1751561.831651,
    rent_from: "iste",
    rent_to: "magni",
    pay_start: "unde",
    payment_space: 8,
    deposit_money: 10737.53354,
};

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`PUT api/user/community/contracts/{contract_id}`

#### Body Parameters

| Parameter       | Type    | Status   | Description            |
| --------------- | ------- | -------- | ---------------------- |
| `rental_agent`  | string  | optional | tên người đại diện     |
| `motel_id`      | integer | optional | phòng cho thuê         |
| `money`         | float   | optional | tiền phòng             |
| `rent_from`     | time    | optional | thuê từ ngày           |
| `rent_to`       | time    | optional | thuê đến ngày          |
| `pay_start`     | time    | optional | ngày bắt đầu tính tiền |
| `payment_space` | integer | optional | kỳ thanh toán          |
| `deposit_money` | float   | optional | tiền đặt cọc           |

<!-- END_cab07f2aba66705595db6977ab8e570e -->

#User/Cộng đồng/Phòng yêu thích

<!-- START_f70fd0b289058b85953f437e2d4e2620 -->

## Danh cách phòng yêu thích

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/community/favorite_mo_posts?title=ipsum&money_from=consequuntur&money_to=voluptas&province=sit&district=eum&wards=error&sex=odio&has=odio" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/community/favorite_mo_posts");

let params = {
    title: "ipsum",
    money_from: "consequuntur",
    money_to: "voluptas",
    province: "sit",
    district: "eum",
    wards: "error",
    sex: "odio",
    has: "odio",
};
Object.keys(params).forEach((key) => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/community/favorite_mo_posts`

#### Query Parameters

| Parameter    | Status   | Description                       |
| ------------ | -------- | --------------------------------- |
| `title`      | optional | tìm theo tiêu đề                  |
| `money_from` | optional | tiền tối thiểu                    |
| `money_to`   | optional | tiền tối đa                       |
| `province`   | optional | tỉnh                              |
| `district`   | optional | quận                              |
| `wards`      | optional | huyện                             |
| `sex`        | optional | giới tính                         |
| `has`        | optional | boolean tất cả field có kiểu bool |

<!-- END_f70fd0b289058b85953f437e2d4e2620 -->

<!-- START_505316965da9c5adf3ff11490cb8263c -->

## Yêu thích 1 phòng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/community/favorite_mo_posts/1?is_favorite=alias" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/community/favorite_mo_posts/1");

let params = {
    is_favorite: "alias",
};
Object.keys(params).forEach((key) => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/community/favorite_mo_posts/{mo_post_id}`

#### Query Parameters

| Parameter     | Status   | Description         |
| ------------- | -------- | ------------------- |
| `is_favorite` | optional | yêu thích hay không |

<!-- END_505316965da9c5adf3ff11490cb8263c -->

#User/Cộng đồng/Đơn hàng

<!-- START_16acf0901bf0086b16edbbef98b65dd5 -->

## Đặt hàng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/community/order_service_sells/send" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"phone_number":"amet","name":"fugiat","province":"quas","district":"beatae","wards":"officia","email":"voluptatem","phone":"id","address_detail":"officiis"}'

```

```javascript
const url = new URL(
    "http://localhost/api/user/community/order_service_sells/send"
);

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    phone_number: "amet",
    name: "fugiat",
    province: "quas",
    district: "beatae",
    wards: "officia",
    email: "voluptatem",
    phone: "id",
    address_detail: "officiis",
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/community/order_service_sells/send`

#### Body Parameters

| Parameter        | Type   | Status   | Description |
| ---------------- | ------ | -------- | ----------- |
| `phone_number`   | string | optional | Tên         |
| `name`           | string | optional | Tên         |
| `province`       | string | optional | Tên         |
| `district`       | string | optional | Tên         |
| `wards`          | string | optional | Tên         |
| `email`          | string | optional | Tên         |
| `phone`          | string | optional | Tên         |
| `address_detail` | string | optional | Tên         |

<!-- END_16acf0901bf0086b16edbbef98b65dd5 -->

<!-- START_de49f05178c7779417a803f7e7e2c56f -->

## Danh sách đơn hàng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/community/order_service_sells" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/community/order_service_sells");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/community/order_service_sells`

<!-- END_de49f05178c7779417a803f7e7e2c56f -->

#User/Device token

<!-- START_882728f8fc1393dc0801ae1affd48950 -->

## Đăng ký device token

> Example request:

```bash
curl -X POST \
    "http://localhost/api/device_token_user" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"device_id":"et","device_type":"temporibus","device_token":"aut"}'

```

```javascript
const url = new URL("http://localhost/api/device_token_user");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    device_id: "et",
    device_type: "temporibus",
    device_token: "aut",
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/device_token_user`

#### Body Parameters

| Parameter      | Type   | Status   | Description  |
| -------------- | ------ | -------- | ------------ | ----- |
| `device_id`    | string | required | device_id    |
| `device_type`  | string | required | 0 android    | 1 ios |
| `device_token` | string | required | device_token |

<!-- END_882728f8fc1393dc0801ae1affd48950 -->

#User/Quản lý/Bài đăng tìm phòng trọ

<!-- START_652d2de10530bdc32ce3e14e82501991 -->

## Danh cách phòng đăng tìm phòng trọ

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/manage/mo_posts" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/mo_posts");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/manage/mo_posts`

<!-- END_652d2de10530bdc32ce3e14e82501991 -->

<!-- START_a4d791e7534a0300c7bb0c9e90ebbcc5 -->

## Thong tin bài đăng tìm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/manage/mo_posts/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/mo_posts/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/manage/mo_posts/{post_id}`

<!-- END_a4d791e7534a0300c7bb0c9e90ebbcc5 -->

<!-- START_5df0f5a45ee10718bca4e2bf032e7295 -->

## Xóa 1 phòng trọ

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/user/manage/mo_posts/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/mo_posts/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`DELETE api/user/manage/mo_posts/{post_id}`

#### URL Parameters

| Parameter    | Status   | Description |
| ------------ | -------- | ----------- |
| `store_code` | required | Store code. |

<!-- END_5df0f5a45ee10718bca4e2bf032e7295 -->

#User/Quản lý/Báo cáo thống kê

<!-- START_9a539b7b789faafc3a09e57fb1152da3 -->

## Lấy báo cáo doanh thu theo năm

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/manage/overview" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"date_from":"repellat","date_to":"magnam"}'

```

```javascript
const url = new URL("http://localhost/api/user/manage/overview");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    date_from: "repellat",
    date_to: "magnam",
};

fetch(url, {
    method: "GET",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/manage/overview`

#### Body Parameters

| Parameter   | Type     | Status   | Description                   |
| ----------- | -------- | -------- | ----------------------------- |
| `date_from` | datetime | optional | mốc thời gian tính query      |
| `date_to`   | datetime | optional | đích đến thời gian tính query |

<!-- END_9a539b7b789faafc3a09e57fb1152da3 -->

#User/Quản lý/Hợp đồng

<!-- START_e486d05e50b6664c72bd8cce975c223c -->

## Thêm 1 hợp đồng

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/manage/contracts" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"rental_agent":"atque","motel_id":6,"money":1711864.6,"rent_from":"odio","rent_to":"unde","pay_start":"nostrum","payment_space":16,"deposit_money":37341752.45,"list_renter":"ullam"}'

```

```javascript
const url = new URL("http://localhost/api/user/manage/contracts");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    rental_agent: "atque",
    motel_id: 6,
    money: 1711864.6,
    rent_from: "odio",
    rent_to: "unde",
    pay_start: "nostrum",
    payment_space: 16,
    deposit_money: 37341752.45,
    list_renter: "ullam",
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/manage/contracts`

#### Body Parameters

| Parameter       | Type    | Status   | Description                      |
| --------------- | ------- | -------- | -------------------------------- |
| `rental_agent`  | string  | optional | tên người đại diện               |
| `motel_id`      | integer | optional | phòng cho thuê                   |
| `money`         | float   | optional | tiền phòng                       |
| `rent_from`     | time    | optional | thuê từ ngày                     |
| `rent_to`       | time    | optional | thuê đến ngày                    |
| `pay_start`     | time    | optional | ngày bắt đầu tính tiền           |
| `payment_space` | integer | optional | kỳ thanh toán                    |
| `deposit_money` | float   | optional | tiền đặt cọc                     |
| `list_renter`   | list    | optional | danh sách người thuê [renter_id] |

<!-- END_e486d05e50b6664c72bd8cce975c223c -->

<!-- START_3151aa0c36d1a1c34d111bb6c7eeaa3f -->

## Danh cách hợp đồng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/manage/contracts?contract_status=nobis" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/contracts");

let params = {
    contract_status: "nobis",
};
Object.keys(params).forEach((key) => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/manage/contracts`

#### Query Parameters

| Parameter         | Status   | Description                                      |
| ----------------- | -------- | ------------------------------------------------ |
| `contract_status` | optional | int (2 đang hoạt động,1 quá hạn, 0 đã thanh lý ) |

<!-- END_3151aa0c36d1a1c34d111bb6c7eeaa3f -->

<!-- START_d4f15f642d4b681339a0bbd48b2f5b68 -->

## Thong tin 1 hợp đồng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/manage/contracts/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/contracts/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/manage/contracts/{contract_id}`

<!-- END_d4f15f642d4b681339a0bbd48b2f5b68 -->

<!-- START_021dc328e0b4a9e8d49a4eb03d0b8554 -->

## Xóa 1 hợp đồng

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/user/manage/contracts/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/contracts/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`DELETE api/user/manage/contracts/{contract_id}`

<!-- END_021dc328e0b4a9e8d49a4eb03d0b8554 -->

<!-- START_997ca453cb32c65080288c844620cb2f -->

## Cập nhật 1 hợp đồng

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/user/manage/contracts/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"rental_agent":"nobis","motel_id":7,"money":27.2429,"rent_from":"repellendus","rent_to":"aspernatur","pay_start":"fugiat","payment_space":12,"deposit_money":12.195}'

```

```javascript
const url = new URL("http://localhost/api/user/manage/contracts/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    rental_agent: "nobis",
    motel_id: 7,
    money: 27.2429,
    rent_from: "repellendus",
    rent_to: "aspernatur",
    pay_start: "fugiat",
    payment_space: 12,
    deposit_money: 12.195,
};

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`PUT api/user/manage/contracts/{contract_id}`

#### Body Parameters

| Parameter       | Type    | Status   | Description            |
| --------------- | ------- | -------- | ---------------------- |
| `rental_agent`  | string  | optional | tên người đại diện     |
| `motel_id`      | integer | optional | phòng cho thuê         |
| `money`         | float   | optional | tiền phòng             |
| `rent_from`     | time    | optional | thuê từ ngày           |
| `rent_to`       | time    | optional | thuê đến ngày          |
| `pay_start`     | time    | optional | ngày bắt đầu tính tiền |
| `payment_space` | integer | optional | kỳ thanh toán          |
| `deposit_money` | float   | optional | tiền đặt cọc           |

<!-- END_997ca453cb32c65080288c844620cb2f -->

#User/Quản lý/Người thuê

<!-- START_6634c76e69ec8a2bbe43048d6db542c8 -->

## Thêm 1 người thuê

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/manage/renters" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"similique","phone_number":"cupiditate","email":"molestias","cmnd_number":"magnam","cmnd_front_image_url":"tenetur","cmnd_back_image_url":"velit","address":"doloribus"}'

```

```javascript
const url = new URL("http://localhost/api/user/manage/renters");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    name: "similique",
    phone_number: "cupiditate",
    email: "molestias",
    cmnd_number: "magnam",
    cmnd_front_image_url: "tenetur",
    cmnd_back_image_url: "velit",
    address: "doloribus",
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/manage/renters`

#### Body Parameters

| Parameter              | Type   | Status   | Description        |
| ---------------------- | ------ | -------- | ------------------ |
| `name`                 | string | optional | tên người đại diện |
| `phone_number`         | string | optional | tên người đại diện |
| `email`                | string | optional | tên người đại diện |
| `cmnd_number`          | string | optional | tên người đại diện |
| `cmnd_front_image_url` | string | optional | tên người đại diện |
| `cmnd_back_image_url`  | string | optional | tên người đại diện |
| `address`              | string | optional | tên người đại diện |

<!-- END_6634c76e69ec8a2bbe43048d6db542c8 -->

<!-- START_52f36ef7b4d87f3c9204de30c79099e7 -->

## Danh cách người thuê

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/manage/renters?renter_status=voluptatibus" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/renters");

let params = {
    renter_status: "voluptatibus",
};
Object.keys(params).forEach((key) => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/manage/renters`

#### Query Parameters

| Parameter       | Status   | Description                                       |
| --------------- | -------- | ------------------------------------------------- |
| `renter_status` | optional | int (0 chưa có phòng,2 đang thuê, 1 đã thanh lý ) |

<!-- END_52f36ef7b4d87f3c9204de30c79099e7 -->

<!-- START_549bdc983ed19736afaf9dff11627487 -->

## Thong tin 1 người thuê

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/manage/renters/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/renters/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/manage/renters/{renter_id}`

<!-- END_549bdc983ed19736afaf9dff11627487 -->

<!-- START_30ca4c6b537990c32af5786021e2b7bb -->

## Xóa 1 người thuê

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/user/manage/renters/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/renters/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`DELETE api/user/manage/renters/{renter_id}`

<!-- END_30ca4c6b537990c32af5786021e2b7bb -->

<!-- START_154f000f71c45fb166a9c35f3dd8f085 -->

## Cập nhật 1 người thuê

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/user/manage/renters/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"et","phone_number":"aut","email":"saepe","cmnd_number":"nobis","cmnd_front_image_url":"odit","cmnd_back_image_url":"natus","address":"facilis"}'

```

```javascript
const url = new URL("http://localhost/api/user/manage/renters/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    name: "et",
    phone_number: "aut",
    email: "saepe",
    cmnd_number: "nobis",
    cmnd_front_image_url: "odit",
    cmnd_back_image_url: "natus",
    address: "facilis",
};

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`PUT api/user/manage/renters/{renter_id}`

#### Body Parameters

| Parameter              | Type   | Status   | Description        |
| ---------------------- | ------ | -------- | ------------------ |
| `name`                 | string | optional | tên người đại diện |
| `phone_number`         | string | optional | tên người đại diện |
| `email`                | string | optional | tên người đại diện |
| `cmnd_number`          | string | optional | tên người đại diện |
| `cmnd_front_image_url` | string | optional | tên người đại diện |
| `cmnd_back_image_url`  | string | optional | tên người đại diện |
| `address`              | string | optional | tên người đại diện |

<!-- END_154f000f71c45fb166a9c35f3dd8f085 -->

#User/Quản lý/Phòng trọ

<!-- START_31195c77cb81b5d143e0935a57c0b6f7 -->

## Thêm 1 phòng trọ

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/manage/motels" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"list_service_id":[],"type":5,"status":11,"phone_number":"cupiditate","title":"omnis","description":"ut","motel_name":9,"capacity":11,"sex":19,"area":254449240.6949,"money":3.197,"deposit":7049.5,"electric_money":267.235,"water_money":635112,"has_wifi":"eligendi","wifi_money":"in","has_park":"quidem","park_money":"soluta","province":"nobis","district":"recusandae","wards":"consectetur","address_detail":"consequuntur","has_wc":"facere","has_window":"et","has_security":"a","has_free_move":"enim","has_own_owner":"recusandae","has_air_conditioner":"atque","has_water_heater":"porro","has_kitchen":"qui","has_fridge":"eligendi","has_washing_machine":"eum","has_mezzanine":"nam","has_bed":"et","has_wardrobe":"velit","has_tivi":"quae","has_pet":"veritatis","has_balcony":"omnis","hour_open":"autem","minute_open":"ad","hour_close":"consequatur","minute_close":"dolores"}'

```

```javascript
const url = new URL("http://localhost/api/user/manage/motels");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    list_service_id: [],
    type: 5,
    status: 11,
    phone_number: "cupiditate",
    title: "omnis",
    description: "ut",
    motel_name: 9,
    capacity: 11,
    sex: 19,
    area: 254449240.6949,
    money: 3.197,
    deposit: 7049.5,
    electric_money: 267.235,
    water_money: 635112,
    has_wifi: "eligendi",
    wifi_money: "in",
    has_park: "quidem",
    park_money: "soluta",
    province: "nobis",
    district: "recusandae",
    wards: "consectetur",
    address_detail: "consequuntur",
    has_wc: "facere",
    has_window: "et",
    has_security: "a",
    has_free_move: "enim",
    has_own_owner: "recusandae",
    has_air_conditioner: "atque",
    has_water_heater: "porro",
    has_kitchen: "qui",
    has_fridge: "eligendi",
    has_washing_machine: "eum",
    has_mezzanine: "nam",
    has_bed: "et",
    has_wardrobe: "velit",
    has_tivi: "quae",
    has_pet: "veritatis",
    has_balcony: "omnis",
    hour_open: "autem",
    minute_open: "ad",
    hour_close: "consequatur",
    minute_close: "dolores",
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/manage/motels`

#### Body Parameters

| Parameter             | Type    | Status   | Description                                                         |
| --------------------- | ------- | -------- | ------------------------------------------------------------------- |
| `list_service_id`     | array   | optional | danh sách mã dịch vụ phòng vd: [2,3,5]                              |
| `type`                | integer | optional | 0 phong cho thue, 1 ktx, 2 phong o ghep, 3 nha nguyen can, 4 can ho |
| `status`              | integer | optional | 0 chờ duyệt, 1 bị từ chối, 2 đồng ý                                 |
| `phone_number`        | string  | optional | số người liên hệ cho thuê                                           |
| `title`               | string  | optional | tiêu đề                                                             |
| `description`         | string  | optional | nội dung mô tả                                                      |
| `motel_name`          | integer | optional | số phòng                                                            |
| `capacity`            | integer | optional | sức chứa người/phòng                                                |
| `sex`                 | integer | optional | 0 tất cả, 1 nam , 2 nữ                                              |
| `area`                | float   | optional | diện tích m2                                                        |
| `money`               | float   | optional | số tiền thuê vnd/ phòng                                             |
| `deposit`             | float   | optional | đặt cọc                                                             |
| `electric_money`      | float   | optional | tiền điện - 0 là free                                               |
| `water_money`         | float   | optional | tiền nước tiền nước - 0 là free                                     |
| `has_wifi`            | có      | optional | wifi ko                                                             |
| `wifi_money`          | có      | optional |
| `has_park`            | có      | optional |
| `park_money`          | có      | optional |
| `province`            | có      | optional |
| `district`            | có      | optional |
| `wards`               | có      | optional |
| `address_detail`      | có      | optional |
| `has_wc`              | có      | optional |
| `has_window`          | có      | optional |
| `has_security`        | có      | optional |
| `has_free_move`       | có      | optional |
| `has_own_owner`       | có      | optional |
| `has_air_conditioner` | có      | optional |
| `has_water_heater`    | có      | optional |
| `has_kitchen`         | có      | optional |
| `has_fridge`          | có      | optional |
| `has_washing_machine` | có      | optional |
| `has_mezzanine`       | có      | optional |
| `has_bed`             | có      | optional |
| `has_wardrobe`        | có      | optional |
| `has_tivi`            | có      | optional |
| `has_pet`             | có      | optional |
| `has_balcony`         | có      | optional |
| `hour_open`           | có      | optional |
| `minute_open`         | có      | optional |
| `hour_close`          | có      | optional |
| `minute_close`        | có      | optional |

<!-- END_31195c77cb81b5d143e0935a57c0b6f7 -->

<!-- START_24cee097e2e4ba0eadf9022c293b9cb1 -->

## Danh cách phòng trọ

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/manage/motels" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/motels");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/manage/motels`

<!-- END_24cee097e2e4ba0eadf9022c293b9cb1 -->

<!-- START_3da23c79dbe3219dd5ad405f540d7f6d -->

## Thong tin 1 phòng trọ

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/manage/motels/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/motels/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/manage/motels/{motel_id}`

<!-- END_3da23c79dbe3219dd5ad405f540d7f6d -->

<!-- START_359008a3d037aef3df95f004c1caa1c5 -->

## Xóa 1 phòng trọ

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/user/manage/motels/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/motels/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`DELETE api/user/manage/motels/{motel_id}`

#### URL Parameters

| Parameter    | Status   | Description |
| ------------ | -------- | ----------- |
| `store_code` | required | Store code. |

<!-- END_359008a3d037aef3df95f004c1caa1c5 -->

<!-- START_066e160bd39c5b2e179a402d30b35e78 -->

## Cập nhật 1 phòng trọ

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/user/manage/motels/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"list_mo_service_id":[],"type":13,"status":16,"phone_number":"nesciunt","title":"dolor","description":"enim","motel_name":7,"capacity":9,"sex":16,"area":135539319.1364,"money":15.60886,"deposit":2368381.755,"electric_money":12196337.1336285,"water_money":892.5877983,"has_wifi":"et","wifi_money":"blanditiis","has_park":"architecto","park_money":"quia","province":"ut","district":"quasi","wards":"ratione","address_detail":"quia","has_wc":"fuga","has_window":"nihil","has_security":"molestiae","has_free_move":"blanditiis","has_own_owner":"quidem","has_air_conditioner":"est","has_water_heater":"eaque","has_kitchen":"dolorem","has_fridge":"harum","has_washing_machine":"earum","has_mezzanine":"voluptate","has_bed":"mollitia","has_wardrobe":"sunt","has_tivi":"ullam","has_pet":"odit","has_balcony":"quo","hour_open":"omnis","minute_open":"fuga","hour_close":"minus","minute_close":"quibusdam"}'

```

```javascript
const url = new URL("http://localhost/api/user/manage/motels/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    list_mo_service_id: [],
    type: 13,
    status: 16,
    phone_number: "nesciunt",
    title: "dolor",
    description: "enim",
    motel_name: 7,
    capacity: 9,
    sex: 16,
    area: 135539319.1364,
    money: 15.60886,
    deposit: 2368381.755,
    electric_money: 12196337.1336285,
    water_money: 892.5877983,
    has_wifi: "et",
    wifi_money: "blanditiis",
    has_park: "architecto",
    park_money: "quia",
    province: "ut",
    district: "quasi",
    wards: "ratione",
    address_detail: "quia",
    has_wc: "fuga",
    has_window: "nihil",
    has_security: "molestiae",
    has_free_move: "blanditiis",
    has_own_owner: "quidem",
    has_air_conditioner: "est",
    has_water_heater: "eaque",
    has_kitchen: "dolorem",
    has_fridge: "harum",
    has_washing_machine: "earum",
    has_mezzanine: "voluptate",
    has_bed: "mollitia",
    has_wardrobe: "sunt",
    has_tivi: "ullam",
    has_pet: "odit",
    has_balcony: "quo",
    hour_open: "omnis",
    minute_open: "fuga",
    hour_close: "minus",
    minute_close: "quibusdam",
};

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`PUT api/user/manage/motels/{motel_id}`

#### Body Parameters

| Parameter             | Type    | Status   | Description                                                         |
| --------------------- | ------- | -------- | ------------------------------------------------------------------- |
| `list_mo_service_id`  | array   | optional | danh sách mã dịch vụ phòng vd: [2,3,5]                              |
| `type`                | integer | optional | 0 phong cho thue, 1 ktx, 2 phong o ghep, 3 nha nguyen can, 4 can ho |
| `status`              | integer | optional | 0 chờ duyệt, 1 bị từ chối, 2 đồng ý                                 |
| `phone_number`        | string  | optional | số người liên hệ cho thuê                                           |
| `title`               | string  | optional | tiêu đề                                                             |
| `description`         | string  | optional | nội dung mô tả                                                      |
| `motel_name`          | integer | optional | số phòng                                                            |
| `capacity`            | integer | optional | sức chứa người/phòng                                                |
| `sex`                 | integer | optional | 0 tất cả, 1 nam , 2 nữ                                              |
| `area`                | float   | optional | diện tích m2                                                        |
| `money`               | float   | optional | số tiền thuê vnd/ phòng                                             |
| `deposit`             | float   | optional | đặt cọc                                                             |
| `electric_money`      | float   | optional | tiền điện - 0 là free                                               |
| `water_money`         | float   | optional | tiền nước tiền nước - 0 là free                                     |
| `has_wifi`            | có      | optional | wifi ko                                                             |
| `wifi_money`          | có      | optional |
| `has_park`            | có      | optional |
| `park_money`          | có      | optional |
| `province`            | có      | optional |
| `district`            | có      | optional |
| `wards`               | có      | optional |
| `address_detail`      | có      | optional |
| `has_wc`              | có      | optional |
| `has_window`          | có      | optional |
| `has_security`        | có      | optional |
| `has_free_move`       | có      | optional |
| `has_own_owner`       | có      | optional |
| `has_air_conditioner` | có      | optional |
| `has_water_heater`    | có      | optional |
| `has_kitchen`         | có      | optional |
| `has_fridge`          | có      | optional |
| `has_washing_machine` | có      | optional |
| `has_mezzanine`       | có      | optional |
| `has_bed`             | có      | optional |
| `has_wardrobe`        | có      | optional |
| `has_tivi`            | có      | optional |
| `has_pet`             | có      | optional |
| `has_balcony`         | có      | optional |
| `hour_open`           | có      | optional |
| `minute_open`         | có      | optional |
| `hour_close`          | có      | optional |
| `minute_close`        | có      | optional |

<!-- END_066e160bd39c5b2e179a402d30b35e78 -->

#User/Đăng ký

<!-- START_638687f1aca2f1e69b360d1516c7c1f9 -->

## Register

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/register" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"sed","phone_number":"velit","email":"exercitationem","password":"sint","otp":"nobis","otp_from":"praesentium"}'

```

```javascript
const url = new URL("http://localhost/api/user/register");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    name: "sed",
    phone_number: "velit",
    email: "exercitationem",
    password: "sint",
    otp: "nobis",
    otp_from: "praesentium",
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/register`

#### Body Parameters

| Parameter      | Type   | Status   | Description                                     |
| -------------- | ------ | -------- | ----------------------------------------------- |
| `name`         | string | required | Tên                                             |
| `phone_number` | string | required | Số điện thoại                                   |
| `email`        | string | required | Email                                           |
| `password`     | string | required | Password                                        |
| `otp`          | string | optional | gửi tin nhắn (DV SAHA gửi tới 8085)             |
| `otp_from`     | string | optional | phone(từ sdt) email(từ email) mặc định là phone |

<!-- END_638687f1aca2f1e69b360d1516c7c1f9 -->

#User/Đăng nhập

<!-- START_57e3b4272508c324659e49ba5758c70f -->

## Login

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/login" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"email_or_phone_number":"incidunt","password":"ullam"}'

```

```javascript
const url = new URL("http://localhost/api/user/login");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    email_or_phone_number: "incidunt",
    password: "ullam",
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/login`

#### Body Parameters

| Parameter               | Type   | Status   | Description                          |
| ----------------------- | ------ | -------- | ------------------------------------ |
| `email_or_phone_number` | string | required | (Username, email hoặc số điện thoại) |
| `password`              | string | required | Password                             |

<!-- END_57e3b4272508c324659e49ba5758c70f -->

<!-- START_d66bae11d5c638661df8141d489ce16d -->

## Lấy lại mật khẩu

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/reset_password" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"phone_number":"sed","password":"deserunt","otp":"est","otp_from":"alias"}'

```

```javascript
const url = new URL("http://localhost/api/user/reset_password");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    phone_number: "sed",
    password: "deserunt",
    otp: "est",
    otp_from: "alias",
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/reset_password`

#### Body Parameters

| Parameter      | Type   | Status   | Description                                     |
| -------------- | ------ | -------- | ----------------------------------------------- |
| `phone_number` | string | required | Số điện thoại                                   |
| `password`     | string | required | Mật khẩu mới                                    |
| `otp`          | string | optional | gửi tin nhắn (DV SAHA gửi tới 8085)             |
| `otp_from`     | string | optional | phone(từ sdt) email(từ email) mặc định là phone |

<!-- END_d66bae11d5c638661df8141d489ce16d -->

<!-- START_4834b73267501b803f86da1a891da3e8 -->

## Thay đổi mật khẩu

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/change_password" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"password":"et"}'

```

```javascript
const url = new URL("http://localhost/api/user/change_password");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    password: "et",
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/change_password`

#### Body Parameters

| Parameter  | Type   | Status   | Description  |
| ---------- | ------ | -------- | ------------ |
| `password` | string | required | Mật khẩu mới |

<!-- END_4834b73267501b803f86da1a891da3e8 -->

<!-- START_9c677645c8472b23d34aa26c448f2f86 -->

## Kiểm tra email,phone_number đã tồn tại

Sẽ ưu tiên kiểm tra phone_number (kết quả true tồn tại, false không tồn tại)

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/login/check_exists" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"phone_number":"quia","email":"numquam"}'

```

```javascript
const url = new URL("http://localhost/api/user/login/check_exists");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    phone_number: "quia",
    email: "numquam",
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/login/check_exists`

#### Body Parameters

| Parameter      | Type     | Status   | Description  |
| -------------- | -------- | -------- | ------------ |
| `phone_number` | required | optional | phone_number |
| `email`        | string   | required | email        |

<!-- END_9c677645c8472b23d34aa26c448f2f86 -->

#general

<!-- START_791bf8232e3dfb02f382ebae700906b9 -->

## api/user/handle_receiver_sms

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/handle_receiver_sms" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/handle_receiver_sms");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request

`GET api/user/handle_receiver_sms`

<!-- END_791bf8232e3dfb02f382ebae700906b9 -->

<!-- START_f748e7248b65175b12cb79c5cbe2dcea -->

## api/user/send_email_otp

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/send_email_otp" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/send_email_otp");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/send_email_otp`

<!-- END_f748e7248b65175b12cb79c5cbe2dcea -->

<!-- START_7d1615c93ccb4b96a9faadef4cfced07 -->

## Api home

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/community/home_app" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/community/home_app");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (200):

```json
{
    "code": 200,
    "success": true,
    "msg_code": "SUCCESS",
    "msg": "THÀNH CÔNG",
    "data": {
        "layouts": [
            {
                "title": "Bài đăng mới nhất",
                "type": "MO_POST",
                "list": [
                    {
                        "id": 44,
                        "user_id": 9,
                        "motel_id": "33",
                        "phone_number": "0368521479",
                        "title": "Trọ kiểu mới",
                        "description": "số115",
                        "motel_name": 5,
                        "capacity": 5,
                        "sex": 1,
                        "area": 25,
                        "money": 1500000,
                        "deposit": 1500000,
                        "electric_money": 3000,
                        "water_money": 70000,
                        "has_wifi": true,
                        "wifi_money": 10000,
                        "has_park": false,
                        "park_money": null,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": 1,
                        "district": 5,
                        "wards": 175,
                        "address_detail": "46 Trung Kính",
                        "has_wc": null,
                        "has_window": null,
                        "has_security": null,
                        "has_free_move": false,
                        "has_own_owner": null,
                        "has_air_conditioner": null,
                        "has_water_heater": null,
                        "has_kitchen": true,
                        "has_fridge": true,
                        "has_washing_machine": true,
                        "has_mezzanine": true,
                        "has_bed": true,
                        "has_wardrobe": null,
                        "has_tivi": null,
                        "has_pet": null,
                        "has_balcony": null,
                        "hour_open": 6,
                        "minute_open": 0,
                        "hour_close": 23,
                        "minute_close": 0,
                        "created_at": "2022-08-25T09:02:08.000000Z",
                        "updated_at": "2022-08-25T10:25:05.000000Z",
                        "is_favorite": false
                    },
                    {
                        "id": 43,
                        "user_id": 9,
                        "motel_id": "33",
                        "phone_number": "0368521479",
                        "title": "Trọ kiểu mới",
                        "description": "số115",
                        "motel_name": 5,
                        "capacity": 5,
                        "sex": 1,
                        "area": 25,
                        "money": 2000000,
                        "deposit": 2000000,
                        "electric_money": 3000,
                        "water_money": 70000,
                        "has_wifi": true,
                        "wifi_money": 10000,
                        "has_park": false,
                        "park_money": null,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": 1,
                        "district": 5,
                        "wards": 175,
                        "address_detail": "46 Trung Kính",
                        "has_wc": null,
                        "has_window": null,
                        "has_security": null,
                        "has_free_move": false,
                        "has_own_owner": null,
                        "has_air_conditioner": null,
                        "has_water_heater": null,
                        "has_kitchen": true,
                        "has_fridge": true,
                        "has_washing_machine": true,
                        "has_mezzanine": true,
                        "has_bed": true,
                        "has_wardrobe": null,
                        "has_tivi": null,
                        "has_pet": null,
                        "has_balcony": null,
                        "hour_open": 6,
                        "minute_open": 0,
                        "hour_close": 23,
                        "minute_close": 0,
                        "created_at": "2022-08-25T09:02:08.000000Z",
                        "updated_at": "2022-08-25T10:25:05.000000Z",
                        "is_favorite": false
                    },
                    {
                        "id": 42,
                        "user_id": 9,
                        "motel_id": "33",
                        "phone_number": "0368521479",
                        "title": "Trọ kiểu mới",
                        "description": "số115",
                        "motel_name": 5,
                        "capacity": 5,
                        "sex": 1,
                        "area": 25,
                        "money": 2500000,
                        "deposit": 2500000,
                        "electric_money": 3000,
                        "water_money": 70000,
                        "has_wifi": true,
                        "wifi_money": 10000,
                        "has_park": false,
                        "park_money": null,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": 1,
                        "district": 5,
                        "wards": 175,
                        "address_detail": "46 Trung Kính",
                        "has_wc": null,
                        "has_window": null,
                        "has_security": null,
                        "has_free_move": false,
                        "has_own_owner": null,
                        "has_air_conditioner": null,
                        "has_water_heater": null,
                        "has_kitchen": true,
                        "has_fridge": true,
                        "has_washing_machine": true,
                        "has_mezzanine": true,
                        "has_bed": true,
                        "has_wardrobe": null,
                        "has_tivi": null,
                        "has_pet": null,
                        "has_balcony": null,
                        "hour_open": 6,
                        "minute_open": 0,
                        "hour_close": 23,
                        "minute_close": 0,
                        "created_at": "2022-08-25T09:02:08.000000Z",
                        "updated_at": "2022-08-25T10:25:05.000000Z",
                        "is_favorite": false
                    },
                    {
                        "id": 41,
                        "user_id": 7,
                        "motel_id": "43",
                        "phone_number": "95",
                        "title": "Omfc",
                        "description": "vt",
                        "motel_name": 26,
                        "capacity": 95,
                        "sex": 0,
                        "area": 95,
                        "money": 95,
                        "deposit": 92,
                        "electric_money": 82,
                        "water_money": 92,
                        "has_wifi": true,
                        "wifi_money": null,
                        "has_park": true,
                        "park_money": null,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": null,
                        "district": null,
                        "wards": null,
                        "address_detail": "vr",
                        "has_wc": null,
                        "has_window": true,
                        "has_security": null,
                        "has_free_move": true,
                        "has_own_owner": null,
                        "has_air_conditioner": null,
                        "has_water_heater": true,
                        "has_kitchen": true,
                        "has_fridge": true,
                        "has_washing_machine": null,
                        "has_mezzanine": true,
                        "has_bed": null,
                        "has_wardrobe": null,
                        "has_tivi": true,
                        "has_pet": null,
                        "has_balcony": null,
                        "hour_open": 11,
                        "minute_open": 3,
                        "hour_close": 23,
                        "minute_close": 5,
                        "created_at": "2022-08-30T04:58:57.000000Z",
                        "updated_at": "2022-08-30T04:58:57.000000Z",
                        "is_favorite": false
                    },
                    {
                        "id": 40,
                        "user_id": 7,
                        "motel_id": "43",
                        "phone_number": "95",
                        "title": "OMG",
                        "description": "vt ggdv",
                        "motel_name": 26,
                        "capacity": 100,
                        "sex": 1,
                        "area": 63,
                        "money": 1000,
                        "deposit": 92,
                        "electric_money": 82,
                        "water_money": 92,
                        "has_wifi": false,
                        "wifi_money": 258,
                        "has_park": false,
                        "park_money": 8697,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": null,
                        "district": null,
                        "wards": null,
                        "address_detail": "vr gdg",
                        "has_wc": null,
                        "has_window": true,
                        "has_security": null,
                        "has_free_move": true,
                        "has_own_owner": null,
                        "has_air_conditioner": null,
                        "has_water_heater": true,
                        "has_kitchen": true,
                        "has_fridge": true,
                        "has_washing_machine": null,
                        "has_mezzanine": true,
                        "has_bed": null,
                        "has_wardrobe": null,
                        "has_tivi": true,
                        "has_pet": null,
                        "has_balcony": null,
                        "hour_open": 11,
                        "minute_open": 3,
                        "hour_close": 23,
                        "minute_close": 5,
                        "created_at": "2022-08-30T03:21:21.000000Z",
                        "updated_at": "2022-08-30T04:46:10.000000Z",
                        "is_favorite": false
                    },
                    {
                        "id": 37,
                        "user_id": 8,
                        "motel_id": "31",
                        "phone_number": "0337056362",
                        "title": "tiêu đè",
                        "description": "Mô tả",
                        "motel_name": 44,
                        "capacity": 1,
                        "sex": 1,
                        "area": 25,
                        "money": 10000,
                        "deposit": 10000,
                        "electric_money": 1000,
                        "water_money": 10000,
                        "has_wifi": true,
                        "wifi_money": 10000,
                        "has_park": true,
                        "park_money": 1000,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": null,
                        "district": null,
                        "wards": 1,
                        "address_detail": "xxx",
                        "has_wc": true,
                        "has_window": true,
                        "has_security": true,
                        "has_free_move": true,
                        "has_own_owner": true,
                        "has_air_conditioner": true,
                        "has_water_heater": true,
                        "has_kitchen": true,
                        "has_fridge": true,
                        "has_washing_machine": true,
                        "has_mezzanine": true,
                        "has_bed": true,
                        "has_wardrobe": true,
                        "has_tivi": true,
                        "has_pet": true,
                        "has_balcony": true,
                        "hour_open": 1,
                        "minute_open": 1,
                        "hour_close": 1,
                        "minute_close": 1,
                        "created_at": "2022-08-25T10:28:30.000000Z",
                        "updated_at": "2022-08-25T10:28:30.000000Z",
                        "is_favorite": false
                    },
                    {
                        "id": 36,
                        "user_id": 8,
                        "motel_id": "33",
                        "phone_number": "0368521479",
                        "title": "Trọ kiểu mới",
                        "description": "số115",
                        "motel_name": 5,
                        "capacity": 5,
                        "sex": 1,
                        "area": 25,
                        "money": 2500000,
                        "deposit": 2500000,
                        "electric_money": 3000,
                        "water_money": 70000,
                        "has_wifi": true,
                        "wifi_money": 10000,
                        "has_park": false,
                        "park_money": null,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": 1,
                        "district": 5,
                        "wards": 175,
                        "address_detail": "46 Trung Kính",
                        "has_wc": null,
                        "has_window": null,
                        "has_security": null,
                        "has_free_move": false,
                        "has_own_owner": null,
                        "has_air_conditioner": null,
                        "has_water_heater": null,
                        "has_kitchen": true,
                        "has_fridge": true,
                        "has_washing_machine": true,
                        "has_mezzanine": true,
                        "has_bed": true,
                        "has_wardrobe": null,
                        "has_tivi": null,
                        "has_pet": null,
                        "has_balcony": null,
                        "hour_open": 6,
                        "minute_open": 0,
                        "hour_close": 23,
                        "minute_close": 0,
                        "created_at": "2022-08-25T09:02:08.000000Z",
                        "updated_at": "2022-08-25T10:25:05.000000Z",
                        "is_favorite": false
                    },
                    {
                        "id": 32,
                        "user_id": 7,
                        "motel_id": "29",
                        "phone_number": "0805858588",
                        "title": "ggiu",
                        "description": "sdffcc",
                        "motel_name": 8,
                        "capacity": 1,
                        "sex": 0,
                        "area": 25,
                        "money": 4,
                        "deposit": 5,
                        "electric_money": 8,
                        "water_money": 5,
                        "has_wifi": true,
                        "wifi_money": 0,
                        "has_park": true,
                        "park_money": 0,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": null,
                        "district": null,
                        "wards": 37,
                        "address_detail": "sèc fxdx",
                        "has_wc": null,
                        "has_window": null,
                        "has_security": null,
                        "has_free_move": true,
                        "has_own_owner": null,
                        "has_air_conditioner": null,
                        "has_water_heater": null,
                        "has_kitchen": null,
                        "has_fridge": null,
                        "has_washing_machine": null,
                        "has_mezzanine": true,
                        "has_bed": null,
                        "has_wardrobe": true,
                        "has_tivi": null,
                        "has_pet": true,
                        "has_balcony": true,
                        "hour_open": 0,
                        "minute_open": 0,
                        "hour_close": 0,
                        "minute_close": 0,
                        "created_at": "2022-08-25T03:34:40.000000Z",
                        "updated_at": "2022-08-25T07:27:30.000000Z",
                        "is_favorite": false
                    },
                    {
                        "id": 2,
                        "user_id": 6,
                        "motel_id": "10",
                        "phone_number": "0868914789",
                        "title": "tiêu đè",
                        "description": "Mô tả",
                        "motel_name": 44,
                        "capacity": 1,
                        "sex": 1,
                        "area": 25,
                        "money": 10000,
                        "deposit": 10000,
                        "electric_money": 1000,
                        "water_money": 10000,
                        "has_wifi": true,
                        "wifi_money": 10000,
                        "has_park": true,
                        "park_money": 1000,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": 1,
                        "district": 1,
                        "wards": 1,
                        "address_detail": "xxx",
                        "has_wc": true,
                        "has_window": true,
                        "has_security": true,
                        "has_free_move": true,
                        "has_own_owner": true,
                        "has_air_conditioner": true,
                        "has_water_heater": true,
                        "has_kitchen": true,
                        "has_fridge": true,
                        "has_washing_machine": true,
                        "has_mezzanine": true,
                        "has_bed": true,
                        "has_wardrobe": true,
                        "has_tivi": true,
                        "has_pet": true,
                        "has_balcony": true,
                        "hour_open": 1,
                        "minute_open": 1,
                        "hour_close": 1,
                        "minute_close": 1,
                        "created_at": "2022-08-15T06:15:03.000000Z",
                        "updated_at": "2022-08-15T06:15:03.000000Z",
                        "is_favorite": false
                    },
                    {
                        "id": 1,
                        "user_id": 2,
                        "motel_id": "2",
                        "phone_number": "0868914789",
                        "title": "tiêu đè",
                        "description": "Mô tả",
                        "motel_name": 44,
                        "capacity": 1,
                        "sex": 1,
                        "area": 25,
                        "money": 10000,
                        "deposit": 10000,
                        "electric_money": 1000,
                        "water_money": 10000,
                        "has_wifi": true,
                        "wifi_money": 10000,
                        "has_park": true,
                        "park_money": 1000,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": 1,
                        "district": 1,
                        "wards": 1,
                        "address_detail": "xxx",
                        "has_wc": true,
                        "has_window": true,
                        "has_security": true,
                        "has_free_move": true,
                        "has_own_owner": true,
                        "has_air_conditioner": true,
                        "has_water_heater": true,
                        "has_kitchen": true,
                        "has_fridge": true,
                        "has_washing_machine": true,
                        "has_mezzanine": true,
                        "has_bed": true,
                        "has_wardrobe": true,
                        "has_tivi": true,
                        "has_pet": true,
                        "has_balcony": true,
                        "hour_open": 1,
                        "minute_open": 1,
                        "hour_close": 1,
                        "minute_close": 1,
                        "created_at": "2022-07-30T04:30:24.000000Z",
                        "updated_at": "2022-07-30T04:30:24.000000Z",
                        "is_favorite": false
                    }
                ]
            },
            {
                "title": "Bài viết nổi bật",
                "type": "MO_POST",
                "list": [
                    {
                        "id": 44,
                        "user_id": 9,
                        "motel_id": "33",
                        "phone_number": "0368521479",
                        "title": "Trọ kiểu mới",
                        "description": "số115",
                        "motel_name": 5,
                        "capacity": 5,
                        "sex": 1,
                        "area": 25,
                        "money": 1500000,
                        "deposit": 1500000,
                        "electric_money": 3000,
                        "water_money": 70000,
                        "has_wifi": true,
                        "wifi_money": 10000,
                        "has_park": false,
                        "park_money": null,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": 1,
                        "district": 5,
                        "wards": 175,
                        "address_detail": "46 Trung Kính",
                        "has_wc": null,
                        "has_window": null,
                        "has_security": null,
                        "has_free_move": false,
                        "has_own_owner": null,
                        "has_air_conditioner": null,
                        "has_water_heater": null,
                        "has_kitchen": true,
                        "has_fridge": true,
                        "has_washing_machine": true,
                        "has_mezzanine": true,
                        "has_bed": true,
                        "has_wardrobe": null,
                        "has_tivi": null,
                        "has_pet": null,
                        "has_balcony": null,
                        "hour_open": 6,
                        "minute_open": 0,
                        "hour_close": 23,
                        "minute_close": 0,
                        "created_at": "2022-08-25T09:02:08.000000Z",
                        "updated_at": "2022-08-25T10:25:05.000000Z",
                        "is_favorite": false
                    },
                    {
                        "id": 43,
                        "user_id": 9,
                        "motel_id": "33",
                        "phone_number": "0368521479",
                        "title": "Trọ kiểu mới",
                        "description": "số115",
                        "motel_name": 5,
                        "capacity": 5,
                        "sex": 1,
                        "area": 25,
                        "money": 2000000,
                        "deposit": 2000000,
                        "electric_money": 3000,
                        "water_money": 70000,
                        "has_wifi": true,
                        "wifi_money": 10000,
                        "has_park": false,
                        "park_money": null,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": 1,
                        "district": 5,
                        "wards": 175,
                        "address_detail": "46 Trung Kính",
                        "has_wc": null,
                        "has_window": null,
                        "has_security": null,
                        "has_free_move": false,
                        "has_own_owner": null,
                        "has_air_conditioner": null,
                        "has_water_heater": null,
                        "has_kitchen": true,
                        "has_fridge": true,
                        "has_washing_machine": true,
                        "has_mezzanine": true,
                        "has_bed": true,
                        "has_wardrobe": null,
                        "has_tivi": null,
                        "has_pet": null,
                        "has_balcony": null,
                        "hour_open": 6,
                        "minute_open": 0,
                        "hour_close": 23,
                        "minute_close": 0,
                        "created_at": "2022-08-25T09:02:08.000000Z",
                        "updated_at": "2022-08-25T10:25:05.000000Z",
                        "is_favorite": false
                    },
                    {
                        "id": 42,
                        "user_id": 9,
                        "motel_id": "33",
                        "phone_number": "0368521479",
                        "title": "Trọ kiểu mới",
                        "description": "số115",
                        "motel_name": 5,
                        "capacity": 5,
                        "sex": 1,
                        "area": 25,
                        "money": 2500000,
                        "deposit": 2500000,
                        "electric_money": 3000,
                        "water_money": 70000,
                        "has_wifi": true,
                        "wifi_money": 10000,
                        "has_park": false,
                        "park_money": null,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": 1,
                        "district": 5,
                        "wards": 175,
                        "address_detail": "46 Trung Kính",
                        "has_wc": null,
                        "has_window": null,
                        "has_security": null,
                        "has_free_move": false,
                        "has_own_owner": null,
                        "has_air_conditioner": null,
                        "has_water_heater": null,
                        "has_kitchen": true,
                        "has_fridge": true,
                        "has_washing_machine": true,
                        "has_mezzanine": true,
                        "has_bed": true,
                        "has_wardrobe": null,
                        "has_tivi": null,
                        "has_pet": null,
                        "has_balcony": null,
                        "hour_open": 6,
                        "minute_open": 0,
                        "hour_close": 23,
                        "minute_close": 0,
                        "created_at": "2022-08-25T09:02:08.000000Z",
                        "updated_at": "2022-08-25T10:25:05.000000Z",
                        "is_favorite": false
                    },
                    {
                        "id": 41,
                        "user_id": 7,
                        "motel_id": "43",
                        "phone_number": "95",
                        "title": "Omfc",
                        "description": "vt",
                        "motel_name": 26,
                        "capacity": 95,
                        "sex": 0,
                        "area": 95,
                        "money": 95,
                        "deposit": 92,
                        "electric_money": 82,
                        "water_money": 92,
                        "has_wifi": true,
                        "wifi_money": null,
                        "has_park": true,
                        "park_money": null,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": null,
                        "district": null,
                        "wards": null,
                        "address_detail": "vr",
                        "has_wc": null,
                        "has_window": true,
                        "has_security": null,
                        "has_free_move": true,
                        "has_own_owner": null,
                        "has_air_conditioner": null,
                        "has_water_heater": true,
                        "has_kitchen": true,
                        "has_fridge": true,
                        "has_washing_machine": null,
                        "has_mezzanine": true,
                        "has_bed": null,
                        "has_wardrobe": null,
                        "has_tivi": true,
                        "has_pet": null,
                        "has_balcony": null,
                        "hour_open": 11,
                        "minute_open": 3,
                        "hour_close": 23,
                        "minute_close": 5,
                        "created_at": "2022-08-30T04:58:57.000000Z",
                        "updated_at": "2022-08-30T04:58:57.000000Z",
                        "is_favorite": false
                    },
                    {
                        "id": 40,
                        "user_id": 7,
                        "motel_id": "43",
                        "phone_number": "95",
                        "title": "OMG",
                        "description": "vt ggdv",
                        "motel_name": 26,
                        "capacity": 100,
                        "sex": 1,
                        "area": 63,
                        "money": 1000,
                        "deposit": 92,
                        "electric_money": 82,
                        "water_money": 92,
                        "has_wifi": false,
                        "wifi_money": 258,
                        "has_park": false,
                        "park_money": 8697,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": null,
                        "district": null,
                        "wards": null,
                        "address_detail": "vr gdg",
                        "has_wc": null,
                        "has_window": true,
                        "has_security": null,
                        "has_free_move": true,
                        "has_own_owner": null,
                        "has_air_conditioner": null,
                        "has_water_heater": true,
                        "has_kitchen": true,
                        "has_fridge": true,
                        "has_washing_machine": null,
                        "has_mezzanine": true,
                        "has_bed": null,
                        "has_wardrobe": null,
                        "has_tivi": true,
                        "has_pet": null,
                        "has_balcony": null,
                        "hour_open": 11,
                        "minute_open": 3,
                        "hour_close": 23,
                        "minute_close": 5,
                        "created_at": "2022-08-30T03:21:21.000000Z",
                        "updated_at": "2022-08-30T04:46:10.000000Z",
                        "is_favorite": false
                    },
                    {
                        "id": 37,
                        "user_id": 8,
                        "motel_id": "31",
                        "phone_number": "0337056362",
                        "title": "tiêu đè",
                        "description": "Mô tả",
                        "motel_name": 44,
                        "capacity": 1,
                        "sex": 1,
                        "area": 25,
                        "money": 10000,
                        "deposit": 10000,
                        "electric_money": 1000,
                        "water_money": 10000,
                        "has_wifi": true,
                        "wifi_money": 10000,
                        "has_park": true,
                        "park_money": 1000,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": null,
                        "district": null,
                        "wards": 1,
                        "address_detail": "xxx",
                        "has_wc": true,
                        "has_window": true,
                        "has_security": true,
                        "has_free_move": true,
                        "has_own_owner": true,
                        "has_air_conditioner": true,
                        "has_water_heater": true,
                        "has_kitchen": true,
                        "has_fridge": true,
                        "has_washing_machine": true,
                        "has_mezzanine": true,
                        "has_bed": true,
                        "has_wardrobe": true,
                        "has_tivi": true,
                        "has_pet": true,
                        "has_balcony": true,
                        "hour_open": 1,
                        "minute_open": 1,
                        "hour_close": 1,
                        "minute_close": 1,
                        "created_at": "2022-08-25T10:28:30.000000Z",
                        "updated_at": "2022-08-25T10:28:30.000000Z",
                        "is_favorite": false
                    },
                    {
                        "id": 36,
                        "user_id": 8,
                        "motel_id": "33",
                        "phone_number": "0368521479",
                        "title": "Trọ kiểu mới",
                        "description": "số115",
                        "motel_name": 5,
                        "capacity": 5,
                        "sex": 1,
                        "area": 25,
                        "money": 2500000,
                        "deposit": 2500000,
                        "electric_money": 3000,
                        "water_money": 70000,
                        "has_wifi": true,
                        "wifi_money": 10000,
                        "has_park": false,
                        "park_money": null,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": 1,
                        "district": 5,
                        "wards": 175,
                        "address_detail": "46 Trung Kính",
                        "has_wc": null,
                        "has_window": null,
                        "has_security": null,
                        "has_free_move": false,
                        "has_own_owner": null,
                        "has_air_conditioner": null,
                        "has_water_heater": null,
                        "has_kitchen": true,
                        "has_fridge": true,
                        "has_washing_machine": true,
                        "has_mezzanine": true,
                        "has_bed": true,
                        "has_wardrobe": null,
                        "has_tivi": null,
                        "has_pet": null,
                        "has_balcony": null,
                        "hour_open": 6,
                        "minute_open": 0,
                        "hour_close": 23,
                        "minute_close": 0,
                        "created_at": "2022-08-25T09:02:08.000000Z",
                        "updated_at": "2022-08-25T10:25:05.000000Z",
                        "is_favorite": false
                    },
                    {
                        "id": 32,
                        "user_id": 7,
                        "motel_id": "29",
                        "phone_number": "0805858588",
                        "title": "ggiu",
                        "description": "sdffcc",
                        "motel_name": 8,
                        "capacity": 1,
                        "sex": 0,
                        "area": 25,
                        "money": 4,
                        "deposit": 5,
                        "electric_money": 8,
                        "water_money": 5,
                        "has_wifi": true,
                        "wifi_money": 0,
                        "has_park": true,
                        "park_money": 0,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": null,
                        "district": null,
                        "wards": 37,
                        "address_detail": "sèc fxdx",
                        "has_wc": null,
                        "has_window": null,
                        "has_security": null,
                        "has_free_move": true,
                        "has_own_owner": null,
                        "has_air_conditioner": null,
                        "has_water_heater": null,
                        "has_kitchen": null,
                        "has_fridge": null,
                        "has_washing_machine": null,
                        "has_mezzanine": true,
                        "has_bed": null,
                        "has_wardrobe": true,
                        "has_tivi": null,
                        "has_pet": true,
                        "has_balcony": true,
                        "hour_open": 0,
                        "minute_open": 0,
                        "hour_close": 0,
                        "minute_close": 0,
                        "created_at": "2022-08-25T03:34:40.000000Z",
                        "updated_at": "2022-08-25T07:27:30.000000Z",
                        "is_favorite": false
                    },
                    {
                        "id": 2,
                        "user_id": 6,
                        "motel_id": "10",
                        "phone_number": "0868914789",
                        "title": "tiêu đè",
                        "description": "Mô tả",
                        "motel_name": 44,
                        "capacity": 1,
                        "sex": 1,
                        "area": 25,
                        "money": 10000,
                        "deposit": 10000,
                        "electric_money": 1000,
                        "water_money": 10000,
                        "has_wifi": true,
                        "wifi_money": 10000,
                        "has_park": true,
                        "park_money": 1000,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": 1,
                        "district": 1,
                        "wards": 1,
                        "address_detail": "xxx",
                        "has_wc": true,
                        "has_window": true,
                        "has_security": true,
                        "has_free_move": true,
                        "has_own_owner": true,
                        "has_air_conditioner": true,
                        "has_water_heater": true,
                        "has_kitchen": true,
                        "has_fridge": true,
                        "has_washing_machine": true,
                        "has_mezzanine": true,
                        "has_bed": true,
                        "has_wardrobe": true,
                        "has_tivi": true,
                        "has_pet": true,
                        "has_balcony": true,
                        "hour_open": 1,
                        "minute_open": 1,
                        "hour_close": 1,
                        "minute_close": 1,
                        "created_at": "2022-08-15T06:15:03.000000Z",
                        "updated_at": "2022-08-15T06:15:03.000000Z",
                        "is_favorite": false
                    },
                    {
                        "id": 1,
                        "user_id": 2,
                        "motel_id": "2",
                        "phone_number": "0868914789",
                        "title": "tiêu đè",
                        "description": "Mô tả",
                        "motel_name": 44,
                        "capacity": 1,
                        "sex": 1,
                        "area": 25,
                        "money": 10000,
                        "deposit": 10000,
                        "electric_money": 1000,
                        "water_money": 10000,
                        "has_wifi": true,
                        "wifi_money": 10000,
                        "has_park": true,
                        "park_money": 1000,
                        "province_name": null,
                        "district_name": null,
                        "wards_name": null,
                        "province": 1,
                        "district": 1,
                        "wards": 1,
                        "address_detail": "xxx",
                        "has_wc": true,
                        "has_window": true,
                        "has_security": true,
                        "has_free_move": true,
                        "has_own_owner": true,
                        "has_air_conditioner": true,
                        "has_water_heater": true,
                        "has_kitchen": true,
                        "has_fridge": true,
                        "has_washing_machine": true,
                        "has_mezzanine": true,
                        "has_bed": true,
                        "has_wardrobe": true,
                        "has_tivi": true,
                        "has_pet": true,
                        "has_balcony": true,
                        "hour_open": 1,
                        "minute_open": 1,
                        "hour_close": 1,
                        "minute_close": 1,
                        "created_at": "2022-07-30T04:30:24.000000Z",
                        "updated_at": "2022-07-30T04:30:24.000000Z",
                        "is_favorite": false
                    }
                ]
            }
        ]
    }
}
```

### HTTP Request

`GET api/user/community/home_app`

<!-- END_7d1615c93ccb4b96a9faadef4cfced07 -->

<!-- START_df09d046386e476c80f1d2fa19f056da -->

## Tìm kiếm phòng

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/community/home_app/search?type_motel=sequi&price_from=rerum&price_to=iure&is_verify=voluptatibus&sort=soluta&gender=quia&descending=veritatis" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/community/home_app/search");

let params = {
    type_motel: "sequi",
    price_from: "rerum",
    price_to: "iure",
    is_verify: "voluptatibus",
    sort: "soluta",
    gender: "quia",
    descending: "veritatis",
};
Object.keys(params).forEach((key) => url.searchParams.append(key, params[key]));

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request

`GET api/user/community/home_app/search`

#### Query Parameters

| Parameter    | Status   | Description                        |
| ------------ | -------- | ---------------------------------- |
| `type_motel` | optional | int                                |
| `price_from` | optional | double                             |
| `price_to`   | optional | double                             |
| `is_verify`  | optional | boolean                            |
| `sort`       | optional | [created_at, max_price, min_price] |
| `gender`     | optional | [0 male, 1 female, 2 all]          |
| `descending` | optional | boolean                            |

<!-- END_df09d046386e476c80f1d2fa19f056da -->

<!-- START_d03a451e4c2b8e25e7ab48a8a6cfc83d -->

## Thêm 1 dịch vụ

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/manage/services" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"user_id":3,"service_name":"deleniti","service_icon":"veritatis","service_unit":1184841.43028,"service_charge":50}'

```

```javascript
const url = new URL("http://localhost/api/user/manage/services");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    user_id: 3,
    service_name: "deleniti",
    service_icon: "veritatis",
    service_unit: 1184841.43028,
    service_charge: 50,
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/manage/services`

#### Body Parameters

| Parameter        | Type    | Status   | Description                |
| ---------------- | ------- | -------- | -------------------------- |
| `user_id`        | integer | optional | id user                    |
| `service_name`   | string  | optional | tên dịch vụ                |
| `service_icon`   | string  | optional | icon dịch vụ               |
| `service_unit`   | float   | optional | phí dịch vụ cho mỗi đơn vị |
| `service_charge` | float   | optional | phí dịch vụ cho mỗi đơn vị |

<!-- END_d03a451e4c2b8e25e7ab48a8a6cfc83d -->

<!-- START_2cb2974f624fd521cfa90f7e66fc5bfa -->

## Danh cách dịch vụ

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/manage/services" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/services");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/manage/services`

<!-- END_2cb2974f624fd521cfa90f7e66fc5bfa -->

<!-- START_e84403858c95bf795ddfac614b27b61b -->

## Thong tin 1 dịch vụ

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/manage/services/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/services/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/manage/services/{service_id}`

<!-- END_e84403858c95bf795ddfac614b27b61b -->

<!-- START_d6e1045a22d88e00853b8c84cabcc2bf -->

## Xóa 1 dịch vụ

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/user/manage/services/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/services/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`DELETE api/user/manage/services/{service_id}`

#### URL Parameters

| Parameter    | Status   | Description |
| ------------ | -------- | ----------- |
| `store_code` | required | Store code. |

<!-- END_d6e1045a22d88e00853b8c84cabcc2bf -->

<!-- START_46fa724a3de5756e6a95a990eb260f62 -->

## Cập nhật 1 dịch vụ

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/user/manage/services/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"user_id":3,"service_name":"vel","service_icon":"nihil","service_unit":10640781.2674,"service_charge":142119067.482701}'

```

```javascript
const url = new URL("http://localhost/api/user/manage/services/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    user_id: 3,
    service_name: "vel",
    service_icon: "nihil",
    service_unit: 10640781.2674,
    service_charge: 142119067.482701,
};

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`PUT api/user/manage/services/{service_id}`

#### Body Parameters

| Parameter        | Type    | Status   | Description                |
| ---------------- | ------- | -------- | -------------------------- |
| `user_id`        | integer | optional | id user                    |
| `service_name`   | string  | optional | tên dịch vụ                |
| `service_icon`   | string  | optional | icon dịch vụ               |
| `service_unit`   | float   | optional | phí dịch vụ cho mỗi đơn vị |
| `service_charge` | float   | optional | phí dịch vụ cho mỗi đơn vị |

<!-- END_46fa724a3de5756e6a95a990eb260f62 -->

<!-- START_abcf2aa0425a8ea6119b788e1db2348e -->

## Thêm 1 dịch vụ

> Example request:

```bash
curl -X POST \
    "http://localhost/api/user/manage/mo_services" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"list_service_id":13,"user_id":15,"service_name":"explicabo","service_icon":"unde","service_unit":1795.818606,"service_charge":723885961.4378}'

```

```javascript
const url = new URL("http://localhost/api/user/manage/mo_services");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    list_service_id: 13,
    user_id: 15,
    service_name: "explicabo",
    service_icon: "unde",
    service_unit: 1795.818606,
    service_charge: 723885961.4378,
};

fetch(url, {
    method: "POST",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`POST api/user/manage/mo_services`

#### Body Parameters

| Parameter         | Type    | Status   | Description                |
| ----------------- | ------- | -------- | -------------------------- |
| `list_service_id` | integer | optional | id service_id              |
| `user_id`         | integer | optional | id user                    |
| `service_name`    | string  | optional | tên dịch vụ                |
| `service_icon`    | string  | optional | icon dịch vụ               |
| `service_unit`    | float   | optional | phí dịch vụ cho mỗi đơn vị |
| `service_charge`  | float   | optional | phí dịch vụ cho mỗi đơn vị |

<!-- END_abcf2aa0425a8ea6119b788e1db2348e -->

<!-- START_eb9b03e59c56ed1da5c87169ef50482d -->

## Danh cách dịch vụ

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/manage/mo_services/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/mo_services/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/manage/mo_services/{motel_id}`

<!-- END_eb9b03e59c56ed1da5c87169ef50482d -->

<!-- START_5916d4e769860af192185ba3caa1c750 -->

## Thong tin 1 dịch vụ

> Example request:

```bash
curl -X GET \
    -G "http://localhost/api/user/manage/mo_services/motel_id/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/mo_services/motel_id/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

> Example response (401):

```json
{
    "code": 401,
    "msg_code": "NO_TOKEN",
    "msg": "Chưa đăng nhập bạn không có quyền truy cập",
    "success": false
}
```

### HTTP Request

`GET api/user/manage/mo_services/motel_id/{mo_service_id}`

<!-- END_5916d4e769860af192185ba3caa1c750 -->

<!-- START_621554b64c3c79586d9958ef4d1dc922 -->

## Xóa 1 dịch vụ

> Example request:

```bash
curl -X DELETE \
    "http://localhost/api/user/manage/mo_services/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json"
```

```javascript
const url = new URL("http://localhost/api/user/manage/mo_services/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`DELETE api/user/manage/mo_services/{mo_service_id}`

#### URL Parameters

| Parameter    | Status   | Description |
| ------------ | -------- | ----------- |
| `store_code` | required | Store code. |

<!-- END_621554b64c3c79586d9958ef4d1dc922 -->

<!-- START_d2b1680e76032b0aa6f2d9b8cd36691e -->

## Cập nhật 1 dịch vụ

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/user/manage/mo_services/1" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"user_id":20,"service_name":"et","service_icon":"ut","service_unit":4651.47,"service_charge":9.0283079}'

```

```javascript
const url = new URL("http://localhost/api/user/manage/mo_services/1");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    user_id: 20,
    service_name: "et",
    service_icon: "ut",
    service_unit: 4651.47,
    service_charge: 9.0283079,
};

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`PUT api/user/manage/mo_services/{mo_service_id}`

#### Body Parameters

| Parameter        | Type    | Status   | Description                |
| ---------------- | ------- | -------- | -------------------------- |
| `user_id`        | integer | optional | id user                    |
| `service_name`   | string  | optional | tên dịch vụ                |
| `service_icon`   | string  | optional | icon dịch vụ               |
| `service_unit`   | float   | optional | phí dịch vụ cho mỗi đơn vị |
| `service_charge` | float   | optional | phí dịch vụ cho mỗi đơn vị |

<!-- END_d2b1680e76032b0aa6f2d9b8cd36691e -->

#profile user

<!-- START_2a644d07e3a86fa75c0077fa54cc05b6 -->

## Cập nhật 1 user

> Example request:

```bash
curl -X PUT \
    "http://localhost/api/user/profile" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"name":"nihil","phone_number":"quasi","email":"accusamus","date_of_birth":"atque","avatar_image":"at","sex":true}'

```

```javascript
const url = new URL("http://localhost/api/user/profile");

let headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
};

let body = {
    name: "nihil",
    phone_number: "quasi",
    email: "accusamus",
    date_of_birth: "atque",
    avatar_image: "at",
    sex: true,
};

fetch(url, {
    method: "PUT",
    headers: headers,
    body: body,
})
    .then((response) => response.json())
    .then((json) => console.log(json));
```

### HTTP Request

`PUT api/user/profile`

#### Body Parameters

| Parameter       | Type     | Status   | Description |
| --------------- | -------- | -------- | ----------- |
| `name`          | string   | optional |
| `phone_number`  | string   | optional |
| `email`         | string   | optional |
| `date_of_birth` | datetime | optional |
| `avatar_image`  | string   | optional |
| `sex`           | boolean  | optional |

<!-- END_2a644d07e3a86fa75c0077fa54cc05b6 -->
