Iban kontrol etmek için

```php
<?php

if(Ibanka\Iban::dogru("TR18 0001 0000 2029 5163 9550 02")) {
    echo "Iban geçerli";
};
```

Banka ve şube bilgileri için

```php
<?php

$data = Ibanka\Iban::bak("TR18 0001 0000 2029 5163 9550 02");
print_r($data);
```

```text
Array
(
    [banka_id] => 0010
    [banka_il] => Ankara
    [adres] => Anafartalar Mahallesi Atatürk Bulvari No:8 06050 Altindağ/ankara
    [banka_isim] => Türkiye Cumhuriyeti Ziraat Bankasi A.ş.
    [sube_id] => 00020
    [il_kodu] => 054
    [sube_isim] => Hendek/sakarya Şubesi
)
```
