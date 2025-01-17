# Dil dosyalarının yolu
LANG_DIR = i18n/languages

# Spesifik .po dosyaları
PO_FILES = $(LANG_DIR)/woocommerce-iyzico-en_US.po \
           $(LANG_DIR)/woocommerce-iyzico-tr_TR.po

# .mo dosyaları için hedefler
MO_FILES = $(PO_FILES:.po=.mo)

# Dil dosyalarını oluştur
create-lang-files: $(MO_FILES)
	@echo "Dil dosyaları oluşturuldu."

# .mo dosyalarını oluştur
%.mo: %.po
	msgfmt -o $@ $<

# Temizleme hedefi
clean-lang-files:
	rm -f $(MO_FILES)
	@echo "Dil dosyaları temizlendi."

# .PHONY tanımlamaları
.PHONY: clean-lang-files create-lang-files help

# Yardım mesajı
help:
	@echo "Kullanılabilir komutlar:"
	@echo "  make create-lang-files       - Dil dosyalarını .mo formatına çevirir"
	@echo "  make clean-lang-files - Oluşturulan .mo dosyalarını siler"
	@echo "  make help             - Bu yardım mesajını gösterir"