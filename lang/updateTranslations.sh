# Extract translatable strings into the template
xgettext ../*.php \
    --from-code=UTF-8 \
    --default-domain=default \
    --language=PHP \
    --no-wrap \
    --keyword=__ \
    --keyword=_e \
    --package-name=mqTranslate \
    --package-version=2.7 \
    --copyright-holder="Qian Qi" \
    --output mqtranslate.pot

for lang in az_AZ bg_BG cs_CZ da_DK de_DE eo es_CA es_ES fr_FR hu_HU id_ID it_IT ja mk_MK ms_MY nl_NL pl_PL pt_BR pt_PT ro_RO sr_RS sv_SE tr_TR zh_CN; do
    # Create empty files if the do not exist yet
    touch mqtranslate-$lang.po

    # Merge the .po files with the template
    msgmerge --update mqtranslate-$lang.po mqtranslate.pot

    # Convert all .po files into .mo
    pocompile mqtranslate-$lang.po mqtranslate-$lang.mo
done
