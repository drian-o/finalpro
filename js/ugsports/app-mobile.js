! function () {
    var e = {
        855: function () {
            function e() {
                return $(".btn-refresh-wallet").prop("disabled", !0), $(".bal-txt").text(""), $("#mainwallet_amount").val(""), $("#maxSliderApk").text(""), $(".bal-txt").addClass("loader"), json_get("/getBal.php").done((function (e) {
                    $(".bal-txt").removeClass("loader"), $(".bal-txt").text(e.data), $("#mainwallet_amount").attr("value", e.data), $("#mainwallet_amount").val(e.data), $("#maxSliderApk").text(e.data), mainwallet = $("#mainwallet_amount").val(), $(".btn-refresh-wallet").prop("disabled", !1)
                })).fail((function () {
                    $(".bal-txt").removeClass("loader"), $(".btn-refresh-wallet").prop("disabled", !1)
                })), !1
            }
            $(document).on("click", ".btn-refresh-wallet", (function () {
                var e = this;
                return $(this).prop("disabled", !0), $(".bal-txt").text(""), $("#mainwallet_amount").val(""), $("#maxSliderApk").text(""), $(".bal-txt").addClass("loader"), json_get("/getBal.php").done((function (t) {
                    $(".bal-txt").removeClass("loader"), $(".bal-txt").text(t.data), $("#mainwallet_amount").attr("value", t.data), $("#mainwallet_amount").val(t.data), $("#maxSliderApk").text(t.data), mainwallet = $("#mainwallet_amount").val(), $(e).prop("disabled", !1)
                })).fail((function () {
                    $(".bal-txt").removeClass("loader"), $(e).prop("disabled", !1)
                })), !1
            })), $(document).on("click", ".btn-refresh-wallet-ref", (function () {
                var e = this;
                return $(this).prop("disabled", !0), $(".bal-ref-txt").text(""), $(".bal-ref-txt").addClass("loader"), json_get("/getRefWalletBal").done((function (t) {
                    $(".bal-ref-txt").removeClass("loader"), $(".bal-ref-txt").text(t.data), $(e).prop("disabled", !1)
                })).fail((function () {
                    $(".bal-ref-txt").removeClass("loader"), $(e).prop("disabled", !1)
                })), !1
            })), $(document).on("click", ".btn-refresh-IDN", (function (e) {
                return e.isSys || $("#other-game-bals button").prop("disabled", !0), $(".bal-IDN").text(""), json_get("/ajaxIDNBal").done((function (e) {
                    $(".bal-IDN").text(e.data), $("#other-game-bals button").prop("disabled", !1)
                })).fail((function () {
                    $("#other-game-bals button").prop("disabled", !1)
                })), !1
            })), $(document).on("click", ".btn-refresh-hkb", (function (e) {
                return e.isSys || $("#other-game-bals button").prop("disabled", !0), $(".bal-hkb").text(""), json_get("/ajaxHKBBal").done((function (e) {
                    console.log(e.data), $(".bal-hkb").text(e.data), $("#other-game-bals button").prop("disabled", !1)
                })).fail((function () {
                    $("#other-game-bals button").prop("disabled", !1)
                })), !1
            })), $(document).on("click", ".btn-refresh-CMD", (function (e) {
                return e.isSys || $("#other-game-bals button").prop("disabled", !0), $(".bal-CMD").text(""), json_get("/ajaxCMDBal").done((function (e) {
                    $(".bal-CMD").text(e.data), $("#other-game-bals button").prop("disabled", !1)
                })).fail((function () {
                    $("#other-game-bals button").prop("disabled", !1)
                })), !1
            })), $(".js-btn-lottery-more").on("click", (function () {
                $(this).prop("disabled", !0);
                var e = this,
                    t = $(this).data("name"),
                    n = $(this).data("dtrange"),
                    a = $(this).data("code");
                xhr_get("/getHKGPLotteryDetails?date_range=" + n + "&game_name=" + t + "&game_code=" + a).done((function (e) {
                    $("#js-modal-lottery-details").html(e), $(".nifty-modal", "#js-modal-lottery-details").nifty("show")
                })).always((function () {
                    $(e).prop("disabled", !1)
                }))
            })), $(document).on("click", ".btn-refresh-PT", (function (e) {
                return e.isSys || $("#other-game-bals button").prop("disabled", !0), $(".bal-PT").text(""), json_get("/ajaxPTBal").done((function (e) {
                    $(".bal-PT").text(e.data), $("#other-game-bals button").prop("disabled", !1)
                })).fail((function () {
                    $("#other-game-bals button").prop("disabled", !1)
                })), !1
            })), $(document).on("click", ".btn-refresh-918kiss", (function (e) {
                return e.isSys || $("#other-game-bals button").prop("disabled", !0), $(".bal-918kiss").text(""), json_get("/ajax918kissBal").done((function (e) {
                    $(".bal-918kiss").text(e.data), $("#other-game-bals button").prop("disabled", !1)
                })).fail((function () {
                    $("#other-game-bals button").prop("disabled", !1)
                })), !1
            })), $(document).on("click", ".btn-refresh-pussy888", (function (e) {
                return e.isSys || $("#other-game-bals button").prop("disabled", !0), $(".bal-918kiss").text(""), json_get("/ajaxPussy888Bal").done((function (e) {
                    $(".bal-pussy888").text(e.data), $("#other-game-bals button").prop("disabled", !1)
                })).fail((function () {
                    $("#other-game-bals button").prop("disabled", !1)
                })), !1
            })), $(document).on("click", ".btn-tran-IDN", (function () {
                var t = $(".bal-IDN").text();
                if (0 == (t = window.convertToNumber(t))) return sweetAlert(transMsgs.transFailedAmt0);
                $("#other-game-bals button").prop("disabled", !0);
                var n = {
                    amt: t
                };
                return $(".bal-IDN").text(""), json_post("/ajaxIDNTran", n).done((function (t) {
                    return $(".bal-IDN").text(t.data), $("#other-game-bals button").prop("disabled", !1), e(), sweetAlert(transMsgs.transferSuccess, "success", "Success")
                })).fail((function () {
                    $("#other-game-bals button").prop("disabled", !1)
                })), !1
            })), $(document).on("click", ".btn-tran-hkb", (function () {
                var t = $("#bal-hkb").text();
                if (0 == (t = window.convertToNumber(t))) return sweetAlert(transMsgs.transFailedAmt0);
                $("#other-game-bals button").prop("disabled", !0);
                var n = {
                    amt: t
                };
                return $(".bal-hkb").text(""), json_post("/ajaxHKBTran", n).done((function (t) {
                    return $(".bal-hkb").text(t.data.hkbWallet), $("#other-game-bals button").prop("disabled", !1), e(), sweetAlert(transMsgs.transferSuccess, "success", "Success")
                })).fail((function () {
                    $("#other-game-bals button").prop("disabled", !1)
                })), !1
            })), $(document).on("click", ".btn-tran-CMD", (function () {
                var t = $(".bal-CMD").text();
                if (0 == (t = window.convertToNumber(t))) return sweetAlert(transMsgs.transFailedAmt0);
                $("#other-game-bals button").prop("disabled", !0);
                var n = {
                    amt: t
                };
                return $(".bal-CMD").text(""), json_post("/ajaxCMDTran", n).done((function (t) {
                    return $(".bal-CMD").text(t.data), $("#other-game-bals button").prop("disabled", !1), e(), sweetAlert(transMsgs.transferSuccess, "success", "Success")
                })).fail((function () {
                    $("#other-game-bals button").prop("disabled", !1)
                })), !1
            })), $(document).on("click", ".btn-tran-PT", (function () {
                var t = $(".bal-PT").text();
                if (0 == (t = window.convertToNumber(t))) return sweetAlert(transMsgs.transFailedAmt0);
                $("#other-game-bals button").prop("disabled", !0);
                var n = {
                    amt: t
                };
                return $(".bal-PT").text(""), json_post("/ajaxPTTran", n).done((function (t) {
                    return $(".bal-PT").text(t.data), $("#other-game-bals button").prop("disabled", !1), e(), sweetAlert(transMsgs.transferSuccess, "success", "Success")
                })).fail((function () {
                    $("#other-game-bals button").prop("disabled", !1)
                })), !1
            })), $(document).on("click", ".btn-tran-918kiss", (function () {
                var t = $(".bal-918kiss").text();
                if (0 == (t = window.convertToNumber(t))) return sweetAlert(transMsgs.transFailedAmt0);
                $("#other-game-bals button").prop("disabled", !0);
                var n = {
                    amt: t
                };
                return $(".bal-918kiss").text(""), json_post("/ajax918kissTran", n).done((function (t) {
                    return $(".game-bals .btn-refresh-918kiss").trigger("click"), $("#other-game-bals button").prop("disabled", !1), e(), sweetAlert(transMsgs.transferSuccess, "success", "Success")
                })).fail((function () {
                    $("#other-game-bals button").prop("disabled", !1)
                })), !1
            })), $(document).on("click", ".btn-tran-pussy888", (function () {
                var t = $(".bal-pussy888").text();
                if (0 == (t = window.convertToNumber(t))) return sweetAlert(transMsgs.transFailedAmt0);
                $("#other-game-bals button").prop("disabled", !0);
                var n = {
                    amt: t
                };
                return $(".bal-pussy888").text(""), json_post("/ajaxPussy888Tran", n).done((function (t) {
                    return $(".game-bals .btn-refresh-pussy888").trigger("click"), $("#other-game-bals button").prop("disabled", !1), e(), sweetAlert(transMsgs.transferSuccess, "success", "Success")
                })).fail((function () {
                    $("#other-game-bals button").prop("disabled", !1)
                })), !1
            })), window.ajaxLoginForm = function () {
                json_get("/ajaxCSRFToken", showLoadingImgFn, removeLoadingImgFn).done((function (e) {
                    $("#sec_token--loginForm").val(e.data)
                }))
            }, window.update_memo_status = function (e, t, n) {
                json_post("/update-memo-status", {
                    type: e,
                    msg_id: t,
                    mode: n
                }).done((function (e) {
                    Swal.fire("Warning", e.m, "success").then((function () {
                        location.reload()
                    }))
                })).fail((function () {
                    return !1
                }))
            }, window.ajaxResetPwdForm = function () {
                json_get("/ajaxCSRFToken", showLoadingImgFn, removeLoadingImgFn).done((function (e) {
                    $("#sec_token--resetPwdForm").val(e.data)
                }))
            }, window.getAllGameBal = function () {
                window.isAuth && ($(".game-bals .btn-refresh-PT").click(), $(".btn-refresh-PT").click(), $("#other-game-bals button").prop("disabled", !1), $(".game-bals .btn-refresh-CMD").click(), $(".btn-refresh-CMD").click(), $("#other-game-bals button").prop("disabled", !1), "IDR" == window.currencyCode && ($(" .btn-refresh-IDN").click(), $(" .btn-refresh-hkb").click()), "MYR" != window.currencyCode && "THB" != window.currencyCode || ($(".btn-refresh-918kiss").click(), $("#other-game-bals button").prop("disabled", !1), $(".game-bals .btn-refresh-pussy888").click()))
            }, $(document).on("click", ".btn-notifications", (function () {
                return json_get("/api/ajaxGetNotifications", showLoadingImgFn, removeLoadingImgFn).done((function (e) {
                    var t = "";
                    $.each(e.data, (function (e, n) {
                        t += "<li><p>" + n.created_at + "</p><p>" + n.description + "</p>"
                    })), $(".noti_list").html(t)
                })).fail((function () {
                    return !1
                })), !1
            })), window.check_notification_status = function () {
                json_get("/api/ajaxCheckMsgs", null, null).done((function (e) {
                    if (!e) return !1;
                    var t = e.inboxCnt,
                        n = (e.notiCnt, e.memoblinkstate);
                    return t > 0 && n ? ($(".mail_icon, .txt_mail_cnt").text(t), $(".mail_icon").toggle(!0)) : ($(".mail_icon, .txt_mail_cnt").toggle(!1), $(".mail_icon").text(0)), !0
                })).fail((function () {
                    return !1
                }))
            }, $(document).ready((function () {
                var t, n, a, o, i, r = 5;
                "THB" == window.currencyCode && (r = 20), r = $("#twminval").val(), r = parseInt(r), window.tw_information = function () {
                    a = $("#mainwallet_amount").val(), console.log("mainwallet", a), parseInt(a);
                    var e = new RegExp(window.currencyCode + "|,", "gi");
                    t = a.replace(e, ""), n = parseInt(t), o = (o = $("#transfer_amount").val()).replace(/,/g, ""), i = parseInt(o), parseInt(i);
                    $("#slider").slider({
                        value: 0,
                        orientation: "horizontal",
                        range: "min",
                        min: 0,
                        max: n,
                        animate: !0,
                        step: r,
                        change: function (e, n) {
                            $("#transfer_amount").val(n.value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")), console.log(), $("#mainwallet_amount").val((t - n.value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","))
                        }
                    })
                }, $("#tw_increase_btn").click((function () {
                    var e = $("#slider").slider("option", "value"),
                        t = $("#mainwallet_amount").val();
                    t = t.replace(/,/g, ""), (t = parseInt(t)) >= r && ($("#slider").slider("value", e + r), $("#transfer_amount").val((e + r).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")))
                })), $("#tw_decrease_btn").click((function () {
                    var e = $("#slider").slider("option", "value");
                    currentTranAmt = $("#transfer_amount").val(), currentTranAmt <= r ? $("#slider").slider("option", "value", 0) : ($("#slider").slider("value", e - r), $("#transfer_amount").val((e - r).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")))
                })), $("#slider").on("slide", (function (e, n) {
                    $("#transfer_amount").val(n.value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",")), $("#mainwallet_amount").val((t - n.value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                    var a = ($("#transfer_amount").val() || "").replace(/\,/g, "");
                    $("#transfer_amount").val(a)
                })), $("#widget").draggable(), $("#tw_transfer_form").validate({
                    rules: {
                        transfer_amount: {
                            required: !0
                        }
                    },
                    messages: {
                        new_password: {
                            required: "please enter the amount to transfer"
                        }
                    },
                    errorElement: "em",
                    errorPlacement: function (e, t) {
                        e.addClass("help-block"), t.addClass("has-feedback"), "checkbox" === t.prop("type") ? e.insertAfter(t.parent("label")) : e.insertAfter(t), t.next("i")[0] || $("<i class='icon-cancel form-control-feedback absolute'></i>").insertAfter(t)
                    },
                    success: function (e, t) {
                        $(t).next("i")[0] || $("<i class='icon-checkmark  form-control-feedback absolute'></i>").insertAfter($(t))
                    },
                    highlight: function (e, t, n) {
                        $(e).addClass("has-error").removeClass("has-success"), $(e).next("i").addClass("icon-cancel").removeClass("icon-checkmark")
                    },
                    unhighlight: function (e, t, n) {
                        $(e).addClass("has-success").removeClass("has-error"), $(e).next("i").addClass("icon-checkmark").removeClass("icon-cancel")
                    },
                    submitHandler: function (t, n) {
                        $("input[type=submit]").prop("disabled", !0);
                        var a = "#" + t.id;
                        $(a + " input[type=submit]").prop("disabled", !0);
                        var o = $(t).attr("action"),
                            i = $(t).serialize();
                        return json_post(o, i).done((function (n) {
                            $(t).each((function () {
                                this.reset()
                            })), $(t).validate().resetForm(), e(), $(a + " input[type=submit], .btn-primary").prop("disabled", !1), sweetAlert(window.currencyCode + " " + window.commaSeparateNumber(n.data.amount) + " " + transMsgs.walletTranserSuccess, "success", "success").then((function () {
                                window.tw_information(), window.open(launchurl, "_blank")
                            }))
                        })).always((function () {
                            return $(a + " input[type=submit], .btn-primary").prop("disabled", !1), $("#slider").slider("option", "value", 0), !1
                        })), !1
                    }
                })
            }))
        },
        438: function () {
            ! function (e) {
                function t(e, t, n) {
                    var a = "";
                    if (e.responseJSON) {
                        if (a = e.responseJSON.message, e.responseJSON.hasOwnProperty("errors") && e.responseJSON.errors) {
                            var o = e.responseJSON.errors;
                            for (var i in a += "\n Errors :", o) o[i] && (a += "\n" + o[i])
                        }
                    } else a = e.responseText;
                    sweetAlert(a)
                }
                window.xhr_get = function (t) {
                    return e.ajax({
                        url: t,
                        type: "get",
                        beforeSend: showLoadingImgFn
                    }).always((function () {
                        removeLoadingImgFn()
                    })).fail((function (e, t, n) {
                        sweetAlert(e.responseText)
                    }))
                }, window.showLoadingImgFn = function () {
                    e("#loading--layout").nifty("show")
                }, window.removeLoadingImgFn = function () {
                    e("#loading--layout").nifty("hide")
                }, window.json_get = function (t, n, a) {
                    return e.ajax({
                        url: t,
                        method: "GET",
                        type: "get",
                        dataType: "json",
                        beforeSend: function () {
                            n && n()
                        }
                    }).always((function () {
                        a && a()
                    })).fail((function (e, t, n) {
                        sweetAlert(e.responseJSON ? e.responseJSON.message : e.responseText)
                    }))
                }, window.ajax_submit = function (n) {
                    return e.ajax({
                        url: e(n).attr("action"),
                        method: "POST",
                        type: "POST",
                        data: new FormData(n),
                        enctype: "multipart/form-data",
                        processData: !1,
                        contentType: !1,
                        dataType: "json",
                        cache: !1,
                        beforeSend: showLoadingImgFn
                    }).always((function () {
                        removeLoadingImgFn(), e(n).find('button[type="submit"]').prop("disabled", !1)
                    })).fail((function (e, n, a) {
                        t(e, n, a)
                    }))
                }, window.json_post = function (n, a, o, i) {
                    return e.ajax({
                        url: n,
                        method: "POST",
                        type: "POST",
                        data: a,
                        dataType: "json",
                        beforeSend: function () {
                            o && o()
                        }
                    }).always((function () {
                        i && i()
                    })).fail((function (e, n, a) {
                        t(e, n, a)
                    }))
                }, e.ajaxSetup({
                    headers: {
                        "X-CSRF-TOKEN": e('meta[name="csrf-token"]').attr("content")
                    }
                })
            }(jQuery)
        },
        853: function () {
            $(document).on("click", "#bank_deposit_confirm", (function () {
                return $("#regbank_popup__depo").nifty("hide"), $("#btn_add_ubank__depo").prop("disabled", !1), !1
            })), $(document).on("click", "#pulsa_add_cancel", (function () {
                return $("#regbank_popup__depo").nifty("hide"), $("#btn_add_ubank__depo").prop("disabled", !1), !1
            })), window.bindBankRegFormVal = function (e) {
                var t, n, a, o = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : "";
                $(e).submit((function (e) {
                    e.preventDefault()
                })).validate({
                    rules: {
                        acc_name: {
                            required: !0,
                            pattern: /^\s{0,1}[a-zA-Z-.\/,\']+(?:\s[a-zA-Z]+)*\s{0,1}$/,
                            minlength: 3
                        },
                        acc_no: {
                            required: !0,
                            minlength: function (e) {
                                return $("#acc_no").attr("minlength")
                            },
                            maxlength: function (e) {
                                return $("#acc_no").attr("maxlength")
                            },
                            pattern: /^[0-9]+$/,
                            remote: {
                                param: {
                                    url: "/checkAccNo",
                                    type: "post",
                                    dataType: "json",
                                    data: {
                                        acc_type: 5
                                    }
                                }
                            }
                        },
                        new_bank: {
                            required: !0
                        }
                    },
                    messages: {
                        acc_name: {
                            required: transMsgs.accountFullNameRequired,
                            pattern: transMsgs.fullNamesConsistOfAlphabets,
                            minlength: transMsgs.minimumThreeCharatersRequired
                        },
                        acc_no: {
                            required: transMsgs.bankAccountNumberRequired,
                            pattern: transMsgs.bankAccountPattern,
                            minlength: function (e) {
                                var t = $("#acc_no").attr("minlength");
                                transMsgs.minimumEightLetterRequired;
                                return 13 == t ? transMsgs.minimum13LetterRequired : 10 == t ? transMsgs.minimum10LetterRequired : 12 == t ? transMsgs.minimum12LetterRequired : 15 == t ? transMsgs.minimum15LetterRequired : transMsgs.minimumEightLetterRequired
                            },
                            maxlength: transMsgs.maximumTwentycharaters,
                            remote: transMsgs.bankAccountNamesNotAvailable
                        },
                        new_bank: {
                            required: transMsgs.bankRequired
                        }
                    },
                    errorElement: "em",
                    errorPlacement: function (e, t) {
                        e.addClass("help-block"), t.addClass("has-feedback"), "checkbox" === t.prop("type") ? e.insertAfter(t.parent("label")) : e.insertAfter(t), t.next("i")[0] || $("<i class='icon-cancel form-control-feedback absolute'></i>").insertAfter(t)
                    },
                    success: function (e, t) {
                        $(t).next("i")[0] || $("<i class='icon-checkmark  form-control-feedback absolute'></i>").insertAfter($(t))
                    },
                    highlight: function (e, t, n) {
                        $(e).addClass("has-error").removeClass("has-success"), $(e).next("i").addClass("icon-cancel").removeClass("icon-checkmark")
                    },
                    unhighlight: function (e, t, n) {
                        $(e).addClass("has-success").removeClass("has-error"), $(e).next("i").addClass("icon-checkmark").removeClass("icon-cancel")
                    },
                    submitHandler: function (e, t) {
                        $("button[type=submit]").prop("disabled", !0), t.preventDefault();
                        var n = $(e).serialize();
                        return json_post("/add-user-bank", n).done((function (e) {
                            if ($("#regbank_popup__depo").nifty("hide"), $("#btn_add_ubank__depo").prop("disabled", !1), "" != o && "bounus_add_bank" == o) return sweetAlert(e.m, "success", "Success"), location.reload(), !0;
                            var t = $("select#bank_user_id");
                            t.html(""), t.append('<option selected value="">Select <span class="txt_metod_name">Bank</span> </option>');
                            var n = e.data;
                            if (n && n.length > 0) {
                                for (var a = 0; a < n.length; a++) {
                                    1 != n[a].status && ("disble", "disabled='true'");
                                    var i = '<option value="' + n[a].id + '"  data-accname="' + n[a].acc_name + '"  data-metName="' + n[a].method_name + '"  data-method="' + n[a].method + '"><span class="sel-lvl-2">' + n[a].provider_name + "-" + n[a].acc_no + "</span>               </option>";
                                    t.append(i)
                                }
                                $("#full_name") && ($("#full_name").val(e.data[0].acc_name), $("#full_name").prop("readonly", !0))
                            }
                            return t.data("originalHTML", t.html()), window.setBankUserOptions(5), sweetAlert(e.m, "success", "Success"), $("button[type=submit]").prop("disabled", !1), !0
                        })).fail((function () {
                            return $("button[type=submit]").prop("disabled", !1), !1
                        })), !1
                    }
                }), t = $("#acc_no").attr("minlength"), n = $("#acc_no").attr("maxlength"), custom_minLength = t, custom_maxLength = n, $("#new_bank").on("change", (function () {
                    a = $(this).find("option:selected").attr("data-bcode");
                    var e = window.bankAccLength(a, t, n);
                    console.log(e.min_len, e.max_len), $("#acc_no").attr("minlength", e.min_len), $("#acc_no").attr("maxlength", e.max_len)
                }))
            }, window.setBankUserOptions = function (e) {
                var t = $("select#bank_user_id");
                window.restoreOptions(t);
                var n = t.find("option").not("[data-method=" + e + "]").not('[value=""]');
                5 == e && (n = n.not("[data-method=7]")), window.removeOptions(t, n)
            }, window.setOriginalSelect = function (e) {
                null == e.data("originalHTML") && e.data("originalHTML", e.html())
            }, window.removeOptions = function (e, t) {
                window.setOriginalSelect(e), t.remove()
            }, window.restoreOptions = function (e) {
                var t = e.data("originalHTML");
                null != t && e.html(t)
            }, window.bindNewFundRegFormVal = function (e) {
                $(e).submit((function (e) {
                    e.preventDefault()
                })).validate({
                    rules: {
                        acc_no: {
                            required: !0,
                            pattern: /^[0-9]+$/
                        },
                        new_bank: {
                            required: !0
                        },
                        acc_name: {
                            required: !0,
                            pattern: /^[a-z A-Z-.\/,\']+$/,
                            minlength: 3
                        }
                    },
                    messages: {
                        acc_name: {
                            required: transMsgs.mobileNumberRequired,
                            pattern: transMsgs.fullNamesConsistAlphabets,
                            minlength: transMsgs.minimumThreeCharatersRequired
                        },
                        acc_no: {
                            required: transMsgs.mobileNumberRequired,
                            pattern: transMsgs.mobileNumberNumbersOnly,
                            remote: transMsgs.mobileNumberNotAvailable
                        },
                        new_bank: {
                            required: transMsgs.bankRequired
                        }
                    },
                    errorElement: "em",
                    errorPlacement: function (e, t) {
                        e.addClass("help-block"), t.addClass("has-feedback"), "checkbox" === t.prop("type") ? e.insertAfter(t.parent("label")) : e.insertAfter(t), t.next("i")[0] || $("<i class='icon-cancel form-control-feedback absolute'></i>").insertAfter(t)
                    },
                    success: function (e, t) {
                        $(t).next("i")[0] || $("<i class='icon-checkmark  form-control-feedback absolute'></i>").insertAfter($(t))
                    },
                    highlight: function (e, t, n) {
                        $(e).addClass("has-error").removeClass("has-success"), $(e).next("i").addClass("icon-cancel").removeClass("icon-checkmark")
                    },
                    unhighlight: function (e, t, n) {
                        $(e).addClass("has-success").removeClass("has-error"), $(e).next("i").addClass("icon-checkmark").removeClass("icon-cancel")
                    },
                    submitHandler: function (e, t) {
                        $("button[type=submit]").prop("disabled", !0), t.preventDefault();
                        var n = $(e).serialize();
                        return json_post("/add-user-bank", n).done((function (t) {
                            $("#regbank_popup__depo").nifty("hide"), $("#btn_add_ubank__depo").prop("disabled", !1);
                            var n = $("select#bank_user_id");
                            n.html(""), n.append('<option selected value="">Pilih <span class="txt_metod_name">Pulsa</span> </option>');
                            var a = t.data;
                            if (a && a.length > 0) {
                                for (var o = 0; o < a.length; o++) {
                                    1 != a[o].status && ("disble", "disabled='true'");
                                    var i = '<option value="' + a[o].id + '"  data-accname="' + a[o].acc_name + '"  data-metName="' + a[o].method_name + '"  data-method="' + a[o].method + '"><span class="sel-lvl-2">' + a[o].provider_name + "-" + a[o].acc_no + "</span>               </option>";
                                    n.append(i)
                                }
                                $("#full_name") && ($("#full_name").val(t.data[0].acc_name), $("#full_name").prop("readonly", !0))
                            }
                            return n.data("originalHTML", n.html()), window.setBankUserOptions($(e).find('input[name="method"]').val()), sweetAlert(t.m, "success", "Success"), $("button[type=submit]").prop("disabled", !1), !0
                        })).fail((function () {
                            return $("button[type=submit]").prop("disabled", !1), !1
                        })), !1
                    }
                })
            }
        },
        923: function () {
            $((function () {
                $('[data-toggle="tooltip"]').tooltip()
            })), $((function () {
                $('[data-toggle="popover"]').popover()
            })), $((function () {
                var e = window.location.pathname || "";
                e = e.replace(new RegExp("/", "g"), "").toLowerCase(), $('.mdc-tab[data-active="' + e + '"]').addClass("mdc-tab--active"), $('.mdc-tab[data-active="' + e + '"] .mdc-tab-indicator').addClass("mdc-tab-indicator--active")
            }));
            var e = $(".slider-content .btn-box.active");
            if (e.length) {
                var t = e.position().left;
                t = t + $(".slider-content").scrollLeft() - $(".slider-content").width() / 2 + e.outerWidth() / 2, $(".slider-content").animate({
                    scrollLeft: t
                }, "fast")
            }
            window.openLiveChat = function (e, t) {
                if ("undefined" != typeof LiveChatWidget && LiveChatWidget) try {
                    LiveChatWidget.call("maximize"), t && LiveChatWidget.call("set_customer_email", t)
                } catch (e) {
                    console.error(e)
                } else e ? window.popitup(e, "livechat") : alert("Live Chat URL not set")
            }, $(document).on("click", "#btn-close--live-draw-modal", (function () {
                return $("#live-draw-modal").nifty("hide"), $("#img--section-closebtn").trigger("click"), !1
            })), $(document).on("click", "#btn-live-draw-modal", (function () {
                $("#live_draw_table").html("<p>Loading...."), $("#live-draw-modal").nifty("show"), $.get("/getHkgpLiveDraw", (function (e) {
                    $("#live_draw_table").html(e)
                })).always((function () { }))
            })), window.change_lang = function (e) {
                var t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : "";
                return $.ajax({
                    url: "/change_lang/" + e + (t ? "/" + t : ""),
                    type: "get",
                    data: {},
                    success: function (e) {
                        location.reload()
                    }
                }), !1
            }, window.bindChgPassFormJS = function () {
                var e = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[!@#$%^&*()_+])[A-Za-z\d!@#$%^&*()_+]{8,20}$/;
                $("#chgPwdForm").validate({
                    rules: {
                        currentPwd: {
                            required: !0,
                            minlength: 6
                        },
                        newPwd: {
                            required: !0,
                            minlength: 8,
                            maxlength: 20,
                            pattern: e
                        },
                        confirmPwd: {
                            required: !0,
                            minlength: 8,
                            equalTo: "#newPwd"
                        }
                    },
                    messages: {
                        currentPwd: {
                            required: transMsgs.currentPwdRequired,
                            minlength: transMsgs.currentPwd5Chars
                        },
                        newPwd: {
                            required: transMsgs.newPwdRequired,
                            minlength: transMsgs.minimumEightLetterRequired,
                            maxlength: transMsgs.maximumTwentycharaters
                        },
                        confirmPwd: {
                            required: transMsgs.confirmPwdRequired,
                            minlength: transMsgs.minimumEightLetterRequired,
                            maxlength: transMsgs.maximumTwentycharaters,
                            equalTo: transMsgs.confirmPwdNotMatched
                        }
                    },
                    errorElement: "em",
                    errorPlacement: function (e, t) {
                        e.addClass("help-block"), t.addClass("has-feedback"), "checkbox" === t.prop("type") ? e.insertAfter(t.parent("label")) : e.insertAfter(t), t.next("i:not(.toggle-password)")[0] || $("<i class='icon-cancel form-control-feedback absolute'></i>").insertAfter(t)
                    },
                    success: function (e, t) {
                        $(t).next("i:not(.toggle-password)")[0] || $("<i class='icon-checkmark  form-control-feedback absolute'></i>").insertAfter($(t))
                    },
                    highlight: function (e, t, n) {
                        $(e).addClass("has-error").removeClass("has-success"), $(e).next("i:not(.toggle-password)").addClass("icon-cancel").removeClass("icon-checkmark")
                    },
                    unhighlight: function (e, t, n) {
                        $(e).addClass("has-success").removeClass("has-error"), $(e).next("i:not(.toggle-password)").addClass("icon-checkmark").removeClass("icon-cancel")
                    },
                    submitHandler: function (e) {
                        $("#chgPwdForm button[type=submit]").prop("disabled", !0), $(".message--chg-pass").hide();
                        var t = $(e).attr("action"),
                            n = $(e).serialize();
                        json_post(t, n).done((function (t) {
                            $(".message--chg-pass").show(), $(e).each((function () {
                                this.reset()
                            })), $("#chgPwdForm button[type=submit]").prop("disabled", !1)
                        })).fail((function () {
                            $("#chgPwdForm button[type=submit]").prop("disabled", !1)
                        }))
                    }
                })
            }, window.bindChgComplaintFormJS = function () {
                $("#complaint-form").validate({
                    rules: {
                        complaint_name: {
                            required: !0
                        },
                        complaint_subject: {
                            required: !0
                        },
                        complaint_email: {
                            required: !0,
                            email: !0
                        },
                        complaint_message: {
                            required: !0
                        },
                        captcha: {
                            required: !0,
                            minlength: 4,
                            maxlength: 4,
                            remote: {
                                url: "/checkFormCaptcha",
                                type: "post",
                                dataType: "json",
                                complete: function (e) {
                                    "refreshCaptcha" == e.responseJSON && $(".btn-refresh-captcha").trigger("click")
                                }
                            }
                        }
                    },
                    messages: {
                        complaint_name: {
                            required: transMsgs.nameRequired
                        },
                        complaint_subject: {
                            required: transMsgs.subjectRequired
                        },
                        complaint_email: {
                            required: transMsgs.emailRequired,
                            email: transMsgs.emailRequired
                        },
                        complaint_message: {
                            required: transMsgs.messageRequired
                        },
                        captcha: {
                            required: transMsgs.captchaRequired,
                            remote: transMsgs.captchaRequired
                        }
                    },
                    errorElement: "em",
                    errorPlacement: function (e, t) {
                        e.addClass("help-block"), t.addClass("has-feedback"), "checkbox" === t.prop("type") ? e.insertAfter(t.parent("label")) : e.insertAfter(t), t.next("i")[0] || $("<i class='icon-cancel form-control-feedback absolute'></i>").insertAfter(t)
                    },
                    success: function (e, t) {
                        $(t).next("i")[0] || $("<i class='icon-checkmark  form-control-feedback absolute'></i>").insertAfter($(t))
                    },
                    highlight: function (e, t, n) {
                        $(e).addClass("has-error").removeClass("has-success"), $(e).next("i").addClass("icon-cancel").removeClass("icon-checkmark")
                    },
                    unhighlight: function (e, t, n) {
                        $(e).addClass("has-success").removeClass("has-error"), $(e).next("i").addClass("icon-checkmark").removeClass("icon-cancel")
                    },
                    submitHandler: function (e) {
                        var t = $(e).attr("action"),
                            n = $(e).serialize();
                        json_post(t, n).done((function (t) {
                            $(".message--chg-pass").show(), $(e).each((function () {
                                this.reset()
                            })), $("#complaint-form button[type=submit]").prop("disabled", !0), (t.status = "s") ? ($(e).each((function () {
                                this.reset()
                            })), $(e).validate().resetForm(), sweetAlert(t.m, "success", transMsgs.success).then((function () {
                                location.reload()
                            }))) : sweetAlert(t.msg)
                        })).fail((function () {
                            $("#complaint-form button[type=submit]").prop("disabled", !1)
                        }))
                    }
                })
            }, $(document).on("click", ".toggle-password", (function () {
                $(this).toggleClass("icon-eye icon-eye-slash");
                var e = $($(this).attr("input_id"));
                return "password" === e.attr("type") ? e.attr("type", "text") : e.attr("type", "password"), !1
            })), $(document).on("click", "#btn-copy--profile-edit", (function () {
                var e = document.getElementById("elCopyText");
                e.focus(), e.select();
                try {
                    document.execCommand("copy");
                    alert(transMsgs.copied + e.value)
                } catch (e) { }
                return !1
            })), $(document).on("click", ".btn-copy", (function () {
                var e = $(this).data("sel"),
                    t = document.getElementById(e);
                t.focus(), t.select();
                try {
                    document.execCommand("copy");
                    alert(transMsgs.copied + t.value)
                } catch (e) { }
                return !1
            })), $(document).on("change", "#isOnSecondPin", (function (e) {
                var t = $("#isOnSecondPin").is(":checked") ? 1 : 0,
                    n = {
                        is_use_second_pin: t
                    };
                console.log(n), json_post("/ajaxUpdate2ndPinFlag", n, window.showLoadingImgFn, window.removeLoadingImgFn).done((function (e) {
                    $("#btn-reset2ndpin").toggle(1 == t)
                }))
            })), $(document).on("change", "#subscribeEmail", (function (e) {
                var t = $("#subscribeEmail").is(":checked") ? 1 : 0,
                    n = {
                        is_email_subscription: t
                    };
                console.log(n), json_post("/ajaxUpdateEmailSubscription", n, window.showLoadingImgFn, window.removeLoadingImgFn).done((function (e) {
                    $("#is_email_subscription").toggle(1 == t)
                }))
            })), $(window).on("load", (function () {
                var e = $("#langSelect").attr("data-selectLang");
                $("#langSelect #" + e).addClass("active")
            })), $(".downloadmodal-trigger").click((function () {
                var e = $(this).attr("data-title");
                window.TWLaunchurl = $(this).attr("data-launchurl");
                var t = $(this).attr("data-apkurl"),
                    n = $(this).attr("data-title").toUpperCase();
                n = "/transferto" + e;
                $("#downloadurl").attr("href", t), $("#downloadgame-title").html(e), $("#gamename").html(e), $("#launchurl").attr("href", window.TWLaunchurl), $("#tw_transfer_form").attr("action", n), $("#apk-modal").addClass("md-show"), window.tw_information(), $("#apk-modal").on("hidden.nifty.modal", (function () {
                    $("#slider").slider("option", "value", 0)
                }))
            }))
        },
        956: function () {
            ! function (e) {
                function t(t) {
                    t.stopPropagation(), t.preventDefault(), e("#mainContent").toggleClass("rightSideBarOpen"), e("#r-side-bar").toggleClass("open"), window.isAuth ? (e("#r-side-bar .side-bar-content").html(""), xhr_get("/ajaxProfile").done((function (t) {
                        e("#r-side-bar .side-bar-content").html(t), e(".profile-head .mdc-tab-bar .mdc-tab[data-tabname=edit]").addClass("active"), e(".profile-head .mdc-tab-bar .mdc-tab[data-tabname=edit] .mdc-tab-indicator").addClass("active"), e(".top-bar .mail_icon").is(":visible") && (e("#r-side-bar .side-bar-content .mail_icon").toggle(!0), e(".side-bar-content .mail_icon,.side-bar-content .txt_mail_cnt").text(e(".top-bar .mail_icon").text()))
                    }))) : e("#loginfreshCaptcha").trigger("click")
                }
                e(document).on("click", "#btnToggleRSideNav", (function (e) {
                    return t(e), !1
                })), e(document).on("click", "#btnLogin--home", (function (e) {
                    return t(e), !1
                })), e(document).on("click", "#btnToggleSideNav", (function (t) {
                    return t.stopPropagation(), t.preventDefault(), e("#sideNav").toggleClass("open"), e("#mainContent").toggleClass("sideNavOpen"), !1
                }))
            }(jQuery)
        },
        928: function () {
            var e;

            function t(e) {
                var t = $(e),
                    n = $(e).attr("data-url") + Date.now() + Math.floor(1e8 * Math.random());
                t.attr("src", n)
            }
            $(".message[_login-modal]").hide(), $("#pinForm").hide(), $("#resetPwdForm").hide(), $("#loginForm").validate({
                rules: {
                    user_name: {
                        required: !0,
                        minlength: 4
                    },
                    password: {
                        required: !0,
                        minlength: 8,
                        maxlength: 20
                    },
                    LoginCaptcha: {
                        required: !0
                    }
                },
                messages: {
                    username: {
                        user_name: transMsgs.userNameRequired
                    },
                    password: {
                        required: transMsgs.pwdRequired
                    },
                    LoginCaptcha: {
                        required: transMsgs.captchaRequired
                    }
                },
                errorElement: "em",
                errorPlacement: function (e, t) {
                    e.addClass("help-block"), t.addClass("has-feedback"), "checkbox" === t.prop("type") ? e.insertAfter(t.parent("label")) : e.insertAfter(t), t.next("i:not(.toggle-password)")[0] || $("<i class='icon-cancel form-control-feedback absolute'></i>").insertAfter(t)
                },
                success: function (e, t) {
                    $(t).next("i:not(.toggle-password)")[0] || $("<i class='icon-checkmark  form-control-feedback absolute'></i>").insertAfter($(t))
                },
                highlight: function (e, t, n) {
                    $(e).addClass("has-error").removeClass("has-success"), $(e).next("i:not(.toggle-password):not(.left)").addClass("icon-cancel").removeClass("icon-checkmark")
                },
                unhighlight: function (e, t, n) {
                    $(e).addClass("has-success").removeClass("has-error"), $(e).next("i:not(.toggle-password):not(.left)").addClass("icon-checkmark").removeClass("icon-cancel")
                },
                submitHandler: function (e, t) {
                    t.preventDefault();
                    var n = "#" + e.id;
                    $(n + " button[type=submit], .btn-refresh").prop("disabled", !0);
                    var a = $(e).attr("action"),
                        o = $(e).serialize(),
                        i = $(n).hasClass("js-inline-form");
                    return $(n + " .message").hide(), json_post(a, o).done((function (e) {
                        if ("s" !== e.s) return i ? window.sweetAlert(e.m, "warning", "warning", !1, !0, !0) : ($(n + " button[type=submit], .btn-refresh").prop("disabled", !1), $(n + " .message").toggle(), $(n + " .message").html(e.m), "refreshCaptcha" == e.s && $("#loginfreshCaptcha").trigger("click")), !1;
                        if ("s" == e.s)
                            if (e.redirectUrl)
                                if (!e.action || "validate-pin" != e.action && "setup-pin" != e.action) {
                                    var t = "";
                                    e.data && e.data.isFirstLogin && (t = "?isFirstLogin=1"), window.location.href = e.redirectUrl + t
                                } else {
                                    if ("setup-pin" == e.action) return $("#btn-close--login-modal").trigger("click"), sweetAlert(e.m, "warning", "Warning", !0).then((function (e) {
                                        location.href = "/setup-pin"
                                    })), !1;
                                    i ? ($(".modal-title", "#login-modal--layout").text(transMsgs.validatePin), $("#login-modal--layout").nifty("show")) : $("#loginForm").slideUp(), e.data && e.data.sp && (console.log(e.data.sp), $(".pin-in-grp").html(e.data.sp)), $("#resetPwdForm").hide(), $("#pinForm").fadeIn(), $("#footer--login-modal").hide()
                                }
                            else location.reload()
                    })).always((function () {
                        return $(n + " button[type=submit], .btn-refresh").prop("disabled", !1), $(e).validate().resetForm(), !1
                    })), !1
                }
            }), e = "#resetPwdForm", $(e).validate({
                rules: {
                    email: {
                        required: !0,
                        email: !0
                    },
                    forgotPwCaptchaimg: {
                        required: !0,
                        minlength: 4,
                        maxlength: 4,
                        remote: {
                            url: "/checkForgotPwCaptcha",
                            type: "post",
                            dataType: "json",
                            complete: function (e) {
                                console.log(e), e && "Invalid captcha" == e.responseJSON && $("#fogotrefreshCaptcha").trigger("click")
                            }
                        }
                    }
                },
                messages: {
                    email: {
                        required: transMsgs.emailRequired,
                        email: transMsgs.emailInvalid
                    },
                    forgotPwCaptchaimg: {
                        required: transMsgs.captchaRequired,
                        remote: transMsgs.captchaInvalid,
                        minlength: transMsgs.minimum4LetterRequired
                    }
                },
                errorElement: "em",
                errorPlacement: function (e, t) {
                    e.addClass("help-block"), t.addClass("has-feedback"), "checkbox" === t.prop("type") ? e.insertAfter(t.parent("label")) : e.insertAfter(t), t.next("i")[0] || $("<i class='icon-cancel form-control-feedback absolute'></i>").insertAfter(t)
                },
                success: function (e, t) {
                    $(t).next("i")[0] || $("<i class='icon-checkmark  form-control-feedback absolute'></i>").insertAfter($(t))
                },
                highlight: function (e, t, n) {
                    $(e).addClass("has-error").removeClass("has-success"), $(e).next("i").addClass("icon-cancel").removeClass("icon-checkmark")
                },
                unhighlight: function (e, t, n) {
                    $(e).addClass("has-success").removeClass("has-error"), $(e).next("i").addClass("icon-checkmark").removeClass("icon-cancel")
                },
                submitHandler: function (t, n) {
                    n.preventDefault(), $("button[type=submit]").prop("disabled", !0);
                    var a = $(t).attr("action"),
                        o = $(t).serialize();
                    return $(e + " .alert.message").text("").hide(), json_post(a, o).done((function (t) {
                        $("button[type=submit]").prop("disabled", !1), $(e + " .alert.message").addClass("alert-success").removeClass("alert-danger").text(t.m).show()
                    })).fail((function (t, n, a) {
                        $("button[type=submit]").prop("disabled", !1);
                        var o = t.responseJSON ? t.responseJSON.message : a;
                        return $(e + " .alert.message").removeClass("alert-success").addClass("alert-danger").text(o).show(), !1
                    })), !1
                }
            }), $(document).on("click", "#forgotPwd-btn--login-modal", (function (e) {
                e.preventDefault(), e.stopPropagation(), t("#forgotPwCaptchaimgpath"), $("#resetPwdForm").show(), $("#loginForm").hide(), $("#pinForm").hide()
            })), $(document).on("click", "#btn-back--login-modal", (function (e) {
                return e.preventDefault(), e.stopPropagation(), $("#resetPwdForm").hide(), $("#loginForm").show(), !1
            })), $("#js-btn-forgot-pwd").on("click", (function (e) {
                return e.preventDefault(), e.stopPropagation(), $("#login-modal--layout").nifty("show"), $(".modal-title", "#login-modal--layout").text(transMsgs.forgotPassword), $("#pinForm").hide(), $("#footer--login-modal").hide(), t("#forgotPwCaptchaimgpath"), $("#resetPwdForm").show(), !1
            }));

            function n(e) {
                a = $(e).prev(), $(a).is("[readonly]") ? n(a) : a.focus()
            }

            function o(e) {
                a = $(e).next(), $(a).is("[readonly]") ? o(a) : a.focus()
            }
            $("#loginfreshCaptcha").click((function (e) {
                e.preventDefault(), e.stopPropagation(), $("#LoginCaptcha").val("");
                var t = $("#loginCaptchaimg").attr("data-url") + Date.now() + Math.floor(1e8 * Math.random());
                $("#loginCaptchaimg").attr("src", t)
            })), $("#fogotrefreshCaptcha").click((function (e) {
                e.preventDefault(), e.stopPropagation(), $("#forgotPwCaptchaimg").val(""), t("#forgotPwCaptchaimgpath")
            })), $(document).on("submit", "#pinForm", (function (e) {
                e.preventDefault(), $("#" + this.id + " .btn-submit").prop("disabled", !0);
                var t = "#" + this.id;
                $(t + " .message").hide();
                var n = "";
                if ($(".pin-in-grp input").each((function () {
                    n += this.value
                })), n.length < 6) return $(t + " .btn-submit").prop("disabled", !1), $(t + " .message").show(), $(t + " .message").html("Pin is incomplete"), !1;
                var a = $(this).attr("action"),
                    o = $(this).serialize();
                return $.ajax({
                    url: a,
                    type: "post",
                    data: o,
                    dataType: "json"
                }).done((function (e) {
                    if (console.log("pin form", e), "s" != e.s) return $(t + " .message").show(), $(t + " .message").html(e.m), !1;
                    if (e.redirectUrl) {
                        var n = "";
                        e.data && e.data.isFirstLogin && (n = "?isFirstLogin=1"), window.location.href = e.redirectUrl + n
                    } else location.reload();
                    return !1
                })).always((function (e, n, a) {
                    return $(".pincode").val(""), $(t + " .btn-submit").removeAttr("disabled"), !1
                })), !1
            })), $(document).on("click", ".pinkey", (function () {
                var e = $(this).val();
                if ("Back" != e) return $(this).parents("form").find("input.pincode").each((function () {
                    if (!$(this).val()) return $(this).val(e), $(this).val().length == $(this).attr("maxlength") && $(this).nextAll("input:enabled:not([readonly])")[0] && $(this).nextAll("input:enabled:not([readonly])")[0].focus(), !1
                })), !1
            })), $(document).on("click", "#back_bt", (function (e) {
                return e.preventDefault(), $($(this).parents("form").find(".pincode:not([readonly])").get().reverse()).each((function () {
                    if ($(this).val()) return $(this).val(""), $(this).focus(), !1
                })), !1
            })), $("#pin-in-grp").keyup((function (e) {
                8 == e.keyCode ? n($(this).find(":focus")) : o(e.target);
                return !1
            }))
        },
        117: function () {
            switch (window.lang) {
                case "id":
                    window.transMsgs = {
                        cfTimeout: "Jaringan Terputus, Silakan Coba Refresh kembali",
                        cfChallenge: "Cloudflare perlu meninjau keamanan koneksi Anda sebelum melanjutkan. Silakan Coba Refresh kembali",
                        transFailedAmt0: "Transfer SEMUA ke dompet gagal. Jumlah Transfer harus lebih dari 0",
                        currentPwdRequired: "Kata Sandi Saat Ini diperlukan",
                        currentPwd5Chars: "Kata Sandi saat ini harus lebih dari 5 karakter",
                        newPwdRequired: "Kata Sandi Baru diperlukan",
                        newPwd5Chars: "Kata sandi baru harus lebih dari 5 karakter",
                        confirmPwdRequired: "Konfirmasi Kata Sandi diperlukan",
                        confirmPwd5chars: "Konfirmasi Kata Sandi harus lebih dari 5 karakter",
                        confirmPwdNotMatched: "Konfirmasi Kata Sandi harus cocok dengan Kata Sandi Baru.",
                        copied: "Disalin ke papan klip: ",
                        emailRequired: "Email tidak boleh kosong",
                        emailInvalid: "Email tidak valid",
                        captchaRequired: "Captcha Diperlukan",
                        captchaInvalid: "Captcha tidak valid",
                        minimum4LetterRequired: "Diperlukan minimal 4 karakter",
                        userNameRequired: "Username diperlukan dan tidak boleh kosong",
                        pwdRequired: "Kata sandi diperlukan dan tidak boleh kosong",
                        plsLogin: "Silahkan login terlebih dahulu sebelum membuka permainan",
                        blockedFrGame: "Untuk saat ini Anda tidak dapat bermain permainan ini, silahkan hubungi CS untuk info lebih lanjut.",
                        gameMaintenance: "Game sedang dalam maintenance.",
                        gameComingSoon: "Game ini akan datang",
                        pageComingSoon: "Halaman akan segera hadir",
                        gamePromoBlock: "Game yang Anda klik bukan milik kategori promosi yang sedang berjalan. Setelah promosi selesai, Anda dapat kembali bermain",
                        forgotPwdEmail: "Perubahan password Anda telah dikirim ke Email. Silahkan reset melalui Email Anda yang terdaftar. Jika Anda tidak menemukan pesan email di kotak masuk, silakan periksa di kotak spam / sampah.",
                        pulsaRefNoPlaceholder: "Isi Nomor HP Pengirim atau Serial Number",
                        transferSuccess: "Transfer Berhasil",
                        accountFullNameRequired: "Nama Lengkap Diperlukan",
                        fullNamesConsistOfAlphabets: "Nama lengkap hanya boleh terdiri dari huruf dan spasi, untuk spasi berturut-turut tidak diperbolehkan",
                        minimumThreeCharatersRequired: "Diperlukan minimal 3 karakter",
                        bankRequired: "Bank Diperlukan",
                        bankAccountNumberRequired: "Nomor rekening bank Diperlukan",
                        bankAccountPattern: "Nomor rekening bank hanya boleh terdiri dari angka",
                        minimumEightLetterRequired: "Diperlukan minimal 8 karakter",
                        minimum13LetterRequired: "Diperlukan minimal 13 karakter",
                        minimum10LetterRequired: "Diperlukan minimal 10 karakter",
                        minimum12LetterRequired: "Diperlukan minimal 12 karakter",
                        minimum15LetterRequired: "Diperlukan minimal 15 karakter",
                        maximumTwentycharaters: "Maksimal hanya 20 karakter",
                        bankAccountNamesNotAvailable: "Nomor rekening bank sudah terdaftar",
                        success: "Berhasil",
                        pCodeConfirm: "Kode promo Anda belum dikonfirmasi. Anda yakin ingin melanjutkan?",
                        offlineBank: "BANK SAAT INI OFFLINE, kami akan memproses transaksi Anda setelah bank online. Anda yakin ingin melanjutkan?",
                        walletTranserSuccess: "berhasil ditransfer ke game",
                        fullNamesConsistAlphabets: "Nama lengkap hanya boleh terdiri dari huruf",
                        mobileNumberRequired: "Nomor ponsel diperlukan",
                        mobileNumberNumbersOnly: "Nomor ponsel harus terdiri dari angka saja",
                        mobileNumberNotAvailable: "Nomor ponsel tidak tersedia",
                        more: "LEBIH",
                        forgotPassword: "Lupa kata sandi",
                        validatePin: "Validasi Pin"
                    };
                    break;
                case "th":
                    window.transMsgs = {
                        cfTimeout: "à¸«à¸¡à¸”à¹€à¸§à¸¥à¸²à¸à¸²à¸£à¹€à¸Šà¸·à¹ˆà¸­à¸¡à¸•à¹ˆà¸­ à¹‚à¸›à¸£à¸”à¸¥à¸­à¸‡à¸£à¸µà¹€à¸Ÿà¸£à¸Šà¸­à¸µà¸à¸„à¸£à¸±à¹‰à¸‡",
                        cfChallenge: "Cloudflare à¸ˆà¸³à¹€à¸›à¹‡à¸™à¸•à¹‰à¸­à¸‡à¸•à¸£à¸§à¸ˆà¸ªà¸­à¸šà¸„à¸§à¸²à¸¡à¸›à¸¥à¸­à¸”à¸ à¸±à¸¢à¸‚à¸­à¸‡à¸à¸²à¸£à¹€à¸Šà¸·à¹ˆà¸­à¸¡à¸•à¹ˆà¸­à¸‚à¸­à¸‡à¸„à¸¸à¸“à¸à¹ˆà¸­à¸™à¸”à¸³à¹€à¸™à¸´à¸™à¸à¸²à¸£à¸•à¹ˆà¸­ à¹‚à¸›à¸£à¸”à¸£à¸µà¹€à¸Ÿà¸£à¸Šà¸«à¸™à¹‰à¸²à¸™à¸µà¹‰",
                        transFailedAmt0: "à¹„à¸¡à¹ˆà¸ªà¸²à¸¡à¸²à¸£à¸–à¹‚à¸­à¸™à¸—à¸±à¹‰à¸‡à¸«à¸¡à¸”à¹„à¸›à¸¢à¸±à¸‡à¸à¸£à¸°à¹€à¸›à¹‹à¸²à¹€à¸‡à¸´à¸™à¹„à¸”à¹‰ à¸ˆà¸³à¸™à¸§à¸™à¹€à¸‡à¸´à¸™à¹‚à¸­à¸™à¸ˆà¸°à¸•à¹‰à¸­à¸‡à¸¡à¸²à¸à¸à¸§à¹ˆà¸² 0",
                        currentPwdRequired: "à¸•à¹‰à¸­à¸‡à¸à¸²à¸£à¸£à¸«à¸±à¸ªà¸œà¹ˆà¸²à¸™à¸›à¸±à¸ˆà¸ˆà¸¸à¸šà¸±à¸™",
                        currentPwd5Chars: "à¸£à¸«à¸±à¸ªà¸œà¹ˆà¸²à¸™à¸›à¸±à¸ˆà¸ˆà¸¸à¸šà¸±à¸™à¸ˆà¸°à¸•à¹‰à¸­à¸‡à¹€à¸à¸´à¸™ 5 à¸•à¸±à¸§à¸­à¸±à¸à¸©à¸£",
                        newPwdRequired: "à¸£à¸«à¸±à¸ªà¸œà¹ˆà¸²à¸™à¹ƒà¸«à¸¡à¹ˆà¸•à¹‰à¸­à¸‡à¹„à¸¡à¹ˆà¸§à¹ˆà¸²à¸‡à¹€à¸›à¸¥à¹ˆà¸²",
                        newPwd5Chars: "à¸£à¸«à¸±à¸ªà¸œà¹ˆà¸²à¸™à¹ƒà¸«à¸¡à¹ˆà¸ˆà¸°à¸•à¹‰à¸­à¸‡à¹€à¸à¸´à¸™ 5 à¸•à¸±à¸§à¸­à¸±à¸à¸©à¸£",
                        confirmPwdRequired: "à¸•à¹‰à¸­à¸‡à¸¢à¸·à¸™à¸¢à¸±à¸™à¸£à¸«à¸±à¸ªà¸œà¹ˆà¸²à¸™",
                        confirmPwd5chars: "à¸¢à¸·à¸™à¸¢à¸±à¸™à¸£à¸«à¸±à¸ªà¸œà¹ˆà¸²à¸™à¸ˆà¸°à¸•à¹‰à¸­à¸‡à¹€à¸à¸´à¸™ 5 à¸•à¸±à¸§à¸­à¸±à¸à¸©à¸£",
                        confirmPwdNotMatched: "à¸£à¸«à¸±à¸ªà¸œà¹ˆà¸²à¸™à¸à¸²à¸£à¸¢à¸·à¸™à¸¢à¸±à¸™à¸ˆà¸°à¸•à¹‰à¸­à¸‡à¸•à¸£à¸‡à¸à¸±à¸šà¸£à¸«à¸±à¸ªà¸œà¹ˆà¸²à¸™à¹ƒà¸«à¸¡à¹ˆ",
                        copied: "à¸„à¸±à¸”à¸¥à¸­à¸à¹„à¸›à¸—à¸µà¹ˆà¸„à¸¥à¸´à¸›à¸šà¸­à¸£à¹Œà¸”: ",
                        emailRequired: "à¸­à¸µà¹€à¸¡à¸¥à¸•à¹‰à¸­à¸‡à¹„à¸¡à¹ˆà¸§à¹ˆà¸²à¸‡à¹€à¸›à¸¥à¹ˆà¸²",
                        emailInvalid: "à¸­à¸µà¹€à¸¡à¸¥à¹„à¸¡à¹ˆà¸–à¸¹à¸à¸•à¹‰à¸­à¸‡",
                        captchaRequired: "à¸•à¹‰à¸­à¸‡à¸£à¸°à¸šà¸¸ Captcha",
                        captchaInvalid: "Captcha à¹„à¸¡à¹ˆà¸–à¸¹à¸à¸•à¹‰à¸­à¸‡",
                        minimum4LetterRequired: "à¸•à¹‰à¸­à¸‡à¸¡à¸µà¸•à¸±à¸§à¸¥à¸°à¸„à¸£à¸­à¸¢à¹ˆà¸²à¸‡à¸™à¹‰à¸­à¸¢ 4 à¸•à¸±à¸§",
                        userNameRequired: "à¸•à¹‰à¸­à¸‡à¸£à¸°à¸šà¸¸à¸Šà¸·à¹ˆà¸­à¸œà¸¹à¹‰à¹ƒà¸Šà¹‰à¹à¸¥à¸°à¸•à¹‰à¸­à¸‡à¹„à¸¡à¹ˆà¸§à¹ˆà¸²à¸‡à¹€à¸›à¸¥à¹ˆà¸²",
                        pwdRequired: "à¸•à¹‰à¸­à¸‡à¹ƒà¸Šà¹‰à¸£à¸«à¸±à¸ªà¸œà¹ˆà¸²à¸™à¹à¸¥à¸°à¸•à¹‰à¸­à¸‡à¹„à¸¡à¹ˆà¸§à¹ˆà¸²à¸‡à¹€à¸›à¸¥à¹ˆà¸²",
                        plsLogin: "à¹‚à¸›à¸£à¸”à¹€à¸‚à¹‰à¸²à¸ªà¸¹à¹ˆà¸£à¸°à¸šà¸šà¹€à¸žà¸·à¹ˆà¸­à¸ˆà¸°à¸”à¸³à¹€à¸™à¸´à¸™à¸à¸²à¸£à¸•à¹ˆà¸­",
                        blockedFrGame: "à¸„à¸¸à¸“à¸–à¸¹à¸à¸£à¸°à¸‡à¸±à¸š / à¸–à¸¹à¸à¸šà¸¥à¹‡à¸­à¸à¹„à¸¡à¹ˆà¹ƒà¸«à¹‰à¹€à¸¥à¹ˆà¸™à¹€à¸à¸¡à¸™à¸µà¹‰ à¸à¸£à¸¸à¸“à¸²à¸•à¸´à¸”à¸•à¹ˆà¸­à¸à¹ˆà¸²à¸¢à¸šà¸£à¸´à¸à¸²à¸£à¸¥à¸¹à¸à¸„à¹‰à¸²à¸ªà¸³à¸«à¸£à¸±à¸šà¸‚à¹‰à¸­à¸¡à¸¹à¸¥à¹€à¸žà¸´à¹ˆà¸¡à¹€à¸•à¸´à¸¡",
                        gameMaintenance: "à¹€à¸à¸¡à¸­à¸¢à¸¹à¹ˆà¸£à¸°à¸«à¸§à¹ˆà¸²à¸‡à¸à¸²à¸£à¸šà¸³à¸£à¸¸à¸‡à¸£à¸±à¸à¸©à¸²",
                        gameComingSoon: "à¹€à¸à¸¡à¸à¸³à¸¥à¸±à¸‡à¸ˆà¸°à¸¡à¸²à¸–à¸¶à¸‡",
                        pageComingSoon: "à¹€à¸žà¸ˆà¸à¸³à¸¥à¸±à¸‡à¸ˆà¸°à¸¡à¸²à¹€à¸£à¹‡à¸§à¹†à¸™à¸µà¹‰",
                        gamePromoBlock: "à¹€à¸à¸¡à¸—à¸µà¹ˆà¸„à¸¸à¸“à¸„à¸¥à¸´à¸à¹„à¸¡à¹ˆà¹„à¸”à¹‰à¸­à¸¢à¸¹à¹ˆà¹ƒà¸™à¸«à¸¡à¸§à¸”à¹‚à¸›à¸£à¹‚à¸¡à¸Šà¸±à¹ˆà¸™à¸›à¸±à¸ˆà¸ˆà¸¸à¸šà¸±à¸™ à¸«à¸¥à¸±à¸‡à¸ˆà¸²à¸à¸à¸²à¸£à¹‚à¸›à¸£à¹‚à¸¡à¸•à¸„à¸¸à¸“à¸ªà¸²à¸¡à¸²à¸£à¸–à¸à¸¥à¸±à¸šà¹„à¸›à¸—à¸µà¹ˆà¹€à¸à¸¡",
                        forgotPwdEmail: "à¹‚à¸›à¸£à¸”à¸•à¸£à¸§à¸ˆà¸ªà¸­à¸šà¸­à¸µà¹€à¸¡à¸¥à¸‚à¸­à¸‡à¸„à¸¸à¸“à¸­à¸µà¹€à¸¡à¸¥à¸£à¸µà¹€à¸‹à¹‡à¸•à¸£à¸«à¸±à¸ªà¸œà¹ˆà¸²à¸™à¸–à¸¹à¸à¸ªà¹ˆà¸‡à¹„à¸›à¹à¸¥à¹‰à¸§. à¸«à¸²à¸à¸„à¸¸à¸“à¹„à¸¡à¹ˆà¸žà¸šà¹ƒà¸™à¸à¸¥à¹ˆà¸­à¸‡à¸ˆà¸”à¸«à¸¡à¸²à¸¢à¹‚à¸›à¸£à¸”à¸•à¸£à¸§à¸ˆà¸ªà¸­à¸šà¹ƒà¸™à¸à¸¥à¹ˆà¸­à¸‡à¸ˆà¸”à¸«à¸¡à¸²à¸¢à¸‚à¸¢à¸° / à¸‚à¸¢à¸°",
                        pulsaRefNoPlaceholder: "à¸à¸£à¸­à¸à¸«à¸¡à¸²à¸¢à¹€à¸¥à¸‚à¹‚à¸—à¸£à¸¨à¸±à¸žà¸—à¹Œà¸¡à¸·à¸­à¸–à¸·à¸­à¸«à¸£à¸·à¸­à¸«à¸¡à¸²à¸¢à¹€à¸¥à¸‚à¸‹à¸µà¹€à¸£à¸µà¸¢à¸¥à¸‚à¸­à¸‡à¸œà¸¹à¹‰à¸ªà¹ˆà¸‡",
                        transferSuccess: "à¸–à¹ˆà¸²à¸¢à¹‚à¸­à¸™à¸„à¸§à¸²à¸¡à¸ªà¸³à¹€à¸£à¹‡à¸ˆ",
                        accountFullNameRequired: "à¸•à¹‰à¸­à¸‡à¸£à¸°à¸šà¸¸à¸Šà¸·à¹ˆà¸­à¸™à¸²à¸¡à¸ªà¸à¸¸à¸¥",
                        fullNamesConsistOfAlphabets: "à¸Šà¸·à¹ˆà¸­à¹€à¸•à¹‡à¸¡à¸•à¹‰à¸­à¸‡à¸›à¸£à¸°à¸à¸­à¸šà¸”à¹‰à¸§à¸¢à¸•à¸±à¸§à¸­à¸±à¸à¸©à¸£à¹à¸¥à¸°à¸Šà¹ˆà¸­à¸‡à¸§à¹ˆà¸²à¸‡à¹€à¸”à¸µà¸¢à¸§à¹€à¸—à¹ˆà¸²à¸™à¸±à¹‰à¸™à¹„à¸¡à¹ˆà¸­à¸™à¸¸à¸à¸²à¸•à¹ƒà¸«à¹‰à¹€à¸§à¹‰à¸™à¸§à¸£à¸£à¸„à¸•à¸´à¸”à¸•à¹ˆà¸­à¸à¸±à¸™à¸«à¸¥à¸²à¸¢à¸Šà¹ˆà¸­à¸‡",
                        minimumThreeCharatersRequired: "à¸•à¹‰à¸­à¸‡à¸¡à¸µà¸•à¸±à¸§à¸¥à¸°à¸„à¸£à¸­à¸¢à¹ˆà¸²à¸‡à¸™à¹‰à¸­à¸¢ 3 à¸•à¸±à¸§",
                        bankRequired: "à¸•à¹‰à¸­à¸‡à¹ƒà¸Šà¹‰à¸˜à¸™à¸²à¸„à¸²à¸£",
                        bankAccountNumberRequired: "à¸•à¹‰à¸­à¸‡à¸£à¸°à¸šà¸¸à¸«à¸¡à¸²à¸¢à¹€à¸¥à¸‚à¸šà¸±à¸à¸Šà¸µà¸˜à¸™à¸²à¸„à¸²à¸£",
                        bankAccountPattern: "à¸«à¸¡à¸²à¸¢à¹€à¸¥à¸‚à¸šà¸±à¸à¸Šà¸µà¸˜à¸™à¸²à¸„à¸²à¸£à¸•à¹‰à¸­à¸‡à¸›à¸£à¸°à¸à¸­à¸šà¸”à¹‰à¸§à¸¢à¸•à¸±à¸§à¹€à¸¥à¸‚à¹€à¸—à¹ˆà¸²à¸™à¸±à¹‰à¸™",
                        minimumEightLetterRequired: "à¸•à¹‰à¸­à¸‡à¸¡à¸µà¸•à¸±à¸§à¸¥à¸°à¸„à¸£à¸­à¸¢à¹ˆà¸²à¸‡à¸™à¹‰à¸­à¸¢ 8 à¸•à¸±à¸§",
                        minimum13LetterRequired: "à¸•à¹‰à¸­à¸‡à¸¡à¸µà¸•à¸±à¸§à¸¥à¸°à¸„à¸£à¸­à¸¢à¹ˆà¸²à¸‡à¸™à¹‰à¸­à¸¢ 13 à¸•à¸±à¸§",
                        minimum10LetterRequired: "à¸•à¹‰à¸­à¸‡à¸¡à¸µà¸•à¸±à¸§à¸¥à¸°à¸„à¸£à¸­à¸¢à¹ˆà¸²à¸‡à¸™à¹‰à¸­à¸¢ 10 à¸•à¸±à¸§",
                        minimum12LetterRequired: "à¸•à¹‰à¸­à¸‡à¸¡à¸µà¸•à¸±à¸§à¸¥à¸°à¸„à¸£à¸­à¸¢à¹ˆà¸²à¸‡à¸™à¹‰à¸­à¸¢ 12 à¸•à¸±à¸§",
                        minimum15LetterRequired: "à¸•à¹‰à¸­à¸‡à¸¡à¸µà¸•à¸±à¸§à¸¥à¸°à¸„à¸£à¸­à¸¢à¹ˆà¸²à¸‡à¸™à¹‰à¸­à¸¢ 15 à¸•à¸±à¸§",
                        maximumTwentycharaters: "à¸ªà¸¹à¸‡à¸ªà¸¸à¸” 20 à¸•à¸±à¸§à¸­à¸±à¸à¸©à¸£à¹€à¸—à¹ˆà¸²à¸™à¸±à¹‰à¸™",
                        bankAccountNamesNotAvailable: "à¹„à¸¡à¹ˆà¸¡à¸µà¸Šà¸·à¹ˆà¸­à¸šà¸±à¸à¸Šà¸µà¸˜à¸™à¸²à¸„à¸²à¸£",
                        success: "à¸„à¸§à¸²à¸¡à¸ªà¸³à¹€à¸£à¹‡à¸ˆ",
                        pCodeConfirm: "à¸£à¸«à¸±à¸ªà¹‚à¸›à¸£à¹‚à¸¡à¸Šà¸±à¹ˆà¸™à¸‚à¸­à¸‡à¸„à¸¸à¸“à¸¢à¸±à¸‡à¹„à¸¡à¹ˆà¹„à¸”à¹‰à¸£à¸±à¸šà¸à¸²à¸£à¸¢à¸·à¸™à¸¢à¸±à¸™ à¹à¸™à¹ˆà¹ƒà¸ˆà¹„à¸«à¸¡à¸§à¹ˆà¸²à¸•à¹‰à¸­à¸‡à¸à¸²à¸£à¸”à¸³à¹€à¸™à¸´à¸™à¸à¸²à¸£à¸•à¹ˆà¸­",
                        offlineBank: "à¸˜à¸™à¸²à¸„à¸²à¸£à¹€à¸›à¹‡à¸™à¹à¸šà¸šà¸­à¸­à¸Ÿà¹„à¸¥à¸™à¹Œà¹ƒà¸™à¸›à¸±à¸ˆà¸ˆà¸¸à¸šà¸±à¸™à¹€à¸£à¸²à¸ˆà¸°à¸”à¸³à¹€à¸™à¸´à¸™à¸à¸²à¸£à¸˜à¸¸à¸£à¸à¸£à¸£à¸¡à¸‚à¸­à¸‡à¸„à¸¸à¸“à¸«à¸¥à¸±à¸‡à¸ˆà¸²à¸à¸—à¸µà¹ˆà¸˜à¸™à¸²à¸„à¸²à¸£à¸­à¸­à¸™à¹„à¸¥à¸™à¹Œ à¹à¸™à¹ˆà¹ƒà¸ˆà¹„à¸«à¸¡à¸§à¹ˆà¸²à¸•à¹‰à¸­à¸‡à¸à¸²à¸£à¸”à¸³à¹€à¸™à¸´à¸™à¸à¸²à¸£à¸•à¹ˆà¸­",
                        walletTranserSuccess: "à¹‚à¸­à¸™à¹„à¸›à¸¢à¸±à¸‡à¹€à¸à¸¡à¹€à¸£à¸µà¸¢à¸šà¸£à¹‰à¸­à¸¢à¹à¸¥à¹‰à¸§",
                        fullNamesConsistAlphabets: "à¸Šà¸·à¹ˆà¸­à¹€à¸•à¹‡à¸¡à¸•à¹‰à¸­à¸‡à¸›à¸£à¸°à¸à¸­à¸šà¸”à¹‰à¸§à¸¢à¸•à¸±à¸§à¸­à¸±à¸à¸©à¸£à¹€à¸—à¹ˆà¸²à¸™à¸±à¹‰à¸™",
                        mobileNumberRequired: "à¸ˆà¸³à¹€à¸›à¹‡à¸™à¸•à¹‰à¸­à¸‡à¹ƒà¸Šà¹‰à¸«à¸¡à¸²à¸¢à¹€à¸¥à¸‚à¹‚à¸—à¸£à¸¨à¸±à¸žà¸—à¹Œà¸¡à¸·à¸­à¸–à¸·à¸­",
                        mobileNumberNumbersOnly: "à¸«à¸¡à¸²à¸¢à¹€à¸¥à¸‚à¹‚à¸—à¸£à¸¨à¸±à¸žà¸—à¹Œà¸¡à¸·à¸­à¸–à¸·à¸­à¸•à¹‰à¸­à¸‡à¸›à¸£à¸°à¸à¸­à¸šà¸”à¹‰à¸§à¸¢à¸•à¸±à¸§à¹€à¸¥à¸‚à¹€à¸—à¹ˆà¸²à¸™à¸±à¹‰à¸™",
                        mobileNumberNotAvailable: "à¹„à¸¡à¹ˆà¸¡à¸µà¸«à¸¡à¸²à¸¢à¹€à¸¥à¸‚à¹‚à¸—à¸£à¸¨à¸±à¸žà¸—à¹Œà¸¡à¸·à¸­à¸–à¸·à¸­",
                        more: "à¸¡à¸²à¸à¸à¸§à¹ˆà¸²",
                        forgotPassword: "à¸¥à¸·à¸¡à¸£à¸«à¸±à¸ªà¸œà¹ˆà¸²à¸™",
                        validatePin: "à¸•à¸£à¸§à¸ˆà¸ªà¸­à¸šà¸žà¸´à¸™"
                    };
                    break;
                case "vn":
                    window.transMsgs = {
                        cfTimeout: "Háº¿t thá»i gian káº¿t ná»‘i, Vui lÃ²ng thá»­ LÃ m má»›i láº¡i",
                        cfChallenge: "Cloudflare cáº§n xem láº¡i tÃ­nh báº£o máº­t cá»§a káº¿t ná»‘i cá»§a báº¡n trÆ°á»›c khi tiáº¿p tá»¥c. Vui lÃ²ng lÃ m má»›i trang",
                        transFailedAmt0: "KhÃ´ng thá»ƒ chuyá»ƒn táº¥t cáº£ vÃ o vÃ­. Sá»‘ tiá»n chuyá»ƒn pháº£i lá»›n hÆ¡n 0",
                        currentPwdRequired: "Cáº§n máº­t kháº©u hiá»‡n táº¡i",
                        currentPwd5Chars: "Máº­t kháº©u hiá»‡n táº¡i pháº£i vÆ°á»£t quÃ¡ 5 kÃ½ tá»±",
                        newPwdRequired: "Máº­t kháº©u má»›i khÃ´ng thá»ƒ Ä‘á»ƒ trá»‘ng",
                        newPwd5Chars: "Máº­t kháº©u má»›i pháº£i vÆ°á»£t quÃ¡ 5 kÃ½ tá»±",
                        confirmPwdRequired: "XÃ¡c nháº­n máº­t kháº©u lÃ  báº¯t buá»™c",
                        confirmPwd5chars: "XÃ¡c nháº­n máº­t kháº©u pháº£i vÆ°á»£t quÃ¡ 5 kÃ½ tá»±",
                        confirmPwdNotMatched: "Máº­t kháº©u xÃ¡c nháº­n pháº£i khá»›p vá»›i máº­t kháº©u má»›i.",
                        copied: "Sao chÃ©p vÃ o clipboard: ",
                        emailRequired: "Email khÃ´ng thá»ƒ Ä‘á»ƒ trá»‘ng",
                        captchaRequired: "Captcha báº¯t buá»™c",
                        captchaInvalid: "ÄÃ¢u vao khÃ´ng hÆ¡Ì£p lÃªÌ£",
                        minimum4LetterRequired: "YÃªu cáº§u tá»‘i thiá»ƒu 4 charaters",
                        emailInvalid: "Email khÃ´ng há»£p lá»‡",
                        userNameRequired: "TÃªn ngÆ°á»i dÃ¹ng lÃ  báº¯t buá»™c vÃ  khÃ´ng thá»ƒ Ä‘á»ƒ trá»‘ng",
                        pwdRequired: "Máº­t kháº©u lÃ  báº¯t buá»™c vÃ  khÃ´ng thá»ƒ Ä‘á»ƒ trá»‘ng",
                        plsLogin: "LÃ m Æ¡n Ä‘Äƒng nháº­p Ä‘á»ƒ tiáº¿p tá»¥c",
                        blockedFrGame: "Báº¡n bá»‹ Ä‘Ã¬nh chá»‰ / cháº·n chÆ¡i trÃ² chÆ¡i nÃ y. Vui lÃ²ng liÃªn há»‡ vá»›i dá»‹ch vá»¥ khÃ¡ch hÃ ng Ä‘á»ƒ biáº¿t thÃªm.",
                        gameMaintenance: "TrÃ² chÆ¡i Ä‘ang báº£o trÃ¬.",
                        gameComingSoon: "TrÃ² chÆ¡i sáº½ sá»›m ra máº¯t",
                        pageComingSoon: "Trang sáº½ sá»›m ra máº¯t",
                        gamePromoBlock: "TrÃ² chÆ¡i báº¡n Ä‘Ã£ nháº¥p khÃ´ng thuá»™c danh má»¥c khuyáº¿n mÃ£i hiá»‡n táº¡i. Sau khi quáº£ng cÃ¡o, báº¡n cÃ³ thá»ƒ quay láº¡i trÃ² chÆ¡i",
                        forgotPwdEmail: "Vui lÃ²ng kiá»ƒm tra email cá»§a báº¡n, email máº­t kháº©u Ä‘áº·t láº¡i Ä‘Ã£ Ä‘Æ°á»£c gá»­i. Náº¿u báº¡n khÃ´ng tÃ¬m tháº¥y nÃ³ trong há»™p thÆ° Ä‘áº¿n, vui lÃ²ng kiá»ƒm tra trong há»™p thÆ° rÃ¡c / rÃ¡c.",
                        pulsaRefNoPlaceholder: "Äiá»n sá»‘ Ä‘iá»‡n thoáº¡i di Ä‘á»™ng hoáº·c sá»‘ sÃª-ri cá»§a ngÆ°á»i gá»­i",
                        transferSuccess: "Chuyá»ƒn giao thÃ nh cÃ´ng",
                        accountFullNameRequired: "Há» vÃ  TÃªn YÃªu cáº§u",
                        fullNamesConsistOfAlphabets: "TÃªn Ä‘áº§y Ä‘á»§ chá»‰ Ä‘Æ°á»£c bao gá»“m báº£ng chá»¯ cÃ¡i vÃ  dáº¥u cÃ¡ch Ä‘Æ¡n, khÃ´ng Ä‘Æ°á»£c phÃ©p cÃ³ nhiá»u dáº¥u cÃ¡ch liÃªn tiáº¿p",
                        minimumThreeCharatersRequired: "YÃªu cáº§u tá»‘i thiá»ƒu 3 charaters",
                        bankRequired: "NgÃ¢n hÃ ng yÃªu cáº§u",
                        bankAccountNumberRequired: "Sá»‘ tÃ i khoáº£n ngÃ¢n hÃ ng Báº¯t buá»™c",
                        bankAccountPattern: "Sá»‘ tÃ i khoáº£n ngÃ¢n hÃ ng chá»‰ cÃ³ thá»ƒ bao gá»“m cÃ¡c sá»‘",
                        minimumEightLetterRequired: "YÃªu cáº§u tá»‘i thiá»ƒu 8 charaters",
                        minimum13LetterRequired: "YÃªu cáº§u tá»‘i thiá»ƒu 13 charaters",
                        minimum10LetterRequired: "YÃªu cáº§u tá»‘i thiá»ƒu 10 charaters",
                        minimum12LetterRequired: "YÃªu cáº§u tá»‘i thiá»ƒu 12 charaters",
                        minimum15LetterRequired: "YÃªu cáº§u tá»‘i thiá»ƒu 15 charaters",
                        maximumTwentycharaters: "Chá»‰ tá»‘i Ä‘a 20 charaters",
                        bankAccountNamesNotAvailable: "TÃªn tÃ i khoáº£n ngÃ¢n hÃ ng khÃ´ng cÃ³ sáºµn",
                        success: "Sá»± thÃ nh cÃ´ng",
                        pCodeConfirm: "MÃ£ khuyáº¿n mÃ£i cá»§a báº¡n chÆ°a Ä‘Æ°á»£c xÃ¡c nháº­n. Báº¡n cÃ³ cháº¯c cháº¯n muá»‘n tiáº¿p tá»¥c khÃ´ng?",
                        offlineBank: "NGÃ‚N HÃ€NG HIá»†N Táº I NGOáº I TUYáº¾N, chÃºng tÃ´i sáº½ xá»­ lÃ½ giao dá»‹ch cá»§a báº¡n sau khi ngÃ¢n hÃ ng trá»±c tuyáº¿n. Báº¡n cÃ³ cháº¯c cháº¯n muá»‘n tiáº¿p tá»¥c khÃ´ng?",
                        walletTranserSuccess: "chuyá»ƒn thÃ nh cÃ´ng sang trÃ² chÆ¡i",
                        fullNamesConsistAlphabets: "TÃªn Ä‘áº§y Ä‘á»§ chá»‰ cÃ³ thá»ƒ bao gá»“m cÃ¡c báº£ng chá»¯ cÃ¡i",
                        mobileNumberRequired: "Sá»‘ Ä‘iá»‡n thoáº¡i di Ä‘á»™ng báº¯t buá»™c",
                        mobileNumberNumbersOnly: "Sá»‘ Ä‘iá»‡n thoáº¡i di Ä‘á»™ng chá»‰ cáº§n bao gá»“m cÃ¡c sá»‘",
                        mobileNumberNotAvailable: "Sá»‘ Ä‘iá»‡n thoáº¡i di Ä‘á»™ng khÃ´ng kháº£ dá»¥ng",
                        more: "HÆ N",
                        forgotPassword: "QuÃªn máº­t kháº©u",
                        validatePin: "XÃ¡c thá»±c mÃ£ pin"
                    };
                    break;
                case "cn":
                    window.transMsgs = {
                        cfTimeout: "è¿žæŽ¥è¶…æ—¶ï¼Œè¯·å°è¯•åˆ·æ–°",
                        cfChallenge: "Cloudflare éœ€è¦åœ¨ç»§ç»­ä¹‹å‰æ£€æŸ¥æ‚¨çš„è¿žæŽ¥çš„å®‰å…¨æ€§ã€‚è¯·åˆ·æ–°é¡µé¢",
                        transFailedAmt0: "å°†å…¨éƒ¨è½¬ç§»åˆ°é’±åŒ…å¤±è´¥ã€‚è½¬è´¦é‡‘é¢å¿…é¡»å¤§äºŽ0",
                        currentPwdRequired: "éœ€è¦å½“å‰å¯†ç ",
                        currentPwd5Chars: "å½“å‰å¯†ç å¿…é¡»è¶…è¿‡5ä¸ªå­—ç¬¦",
                        newPwdRequired: "æ–°å¯†ç ä¸èƒ½ä¸ºç©º",
                        newPwd5Chars: "æ–°å¯†ç å¿…é¡»è¶…è¿‡5ä¸ªå­—ç¬¦",
                        confirmPwdRequired: "ç¡®è®¤å¯†ç ä¸ºå¿…å¡«é¡¹",
                        confirmPwd5chars: "ç¡®è®¤å¯†ç å¿…é¡»è¶…è¿‡5ä¸ªå­—ç¬¦",
                        confirmPwdNotMatched: "ç¡®è®¤å¯†ç å¿…é¡»ä¸Žæ–°å¯†ç åŒ¹é…ã€‚",
                        copied: "å¤åˆ¶åˆ°å‰ªè´´æ¿: ",
                        emailRequired: "ç”µå­é‚®ä»¶ä¸èƒ½ä¸ºç©º",
                        emailInvalid: "ç”µé‚®æ— æ•ˆ",
                        captchaRequired: "éœ€è¦éªŒè¯ç ",
                        captchaInvalid: "æ— æ•ˆè¾“å…¥",
                        minimum4LetterRequired: "è‡³å°‘éœ€è¦4ä¸ªå­—ç¬¦",
                        userNameRequired: "ç”¨æˆ·åä¸ºå¿…å¡«é¡¹ï¼Œä¸èƒ½ä¸ºç©º",
                        pwdRequired: "å¯†ç ä¸ºå¿…å¡«é¡¹ï¼Œä¸èƒ½ä¸ºç©º",
                        plsLogin: "è¯·ç™»å½•è®¿é—®æ›´å¤šå†…å®¹",
                        blockedFrGame: "æ‚¨å·²è¢«æš‚åœ/è¢«é˜»æ­¢çŽ©æ­¤æ¸¸æˆã€‚ è¯·è”ç³»å®¢æœä»¥èŽ·å–æ›´å¤šä¿¡æ¯ã€‚",
                        gameMaintenance: "æ¸¸æˆæ­£åœ¨ç»´æŠ¤ä¸­ã€‚",
                        gameComingSoon: "æ¸¸æˆå³å°†æŽ¨å‡º",
                        pageComingSoon: "è¯¥é¡µé¢å³å°†æŽ¨å‡º",
                        gamePromoBlock: "æ‚¨å•å‡»çš„æ¸¸æˆä¸å±žäºŽå½“å‰ä¿ƒé”€ç±»åˆ«ã€‚ ä¿ƒé”€ç»“æŸåŽï¼Œæ‚¨å¯ä»¥è¿”å›žæ¸¸æˆ",
                        forgotPwdEmail: "è¯·æ£€æŸ¥æ‚¨çš„ç”µå­é‚®ä»¶ï¼Œé‡ç½®å¯†ç ç”µå­é‚®ä»¶å·²å‘é€ã€‚å¦‚æžœæ‚¨æ²¡æœ‰åœ¨æ”¶ä»¶ç®±ä¸­æ‰¾åˆ°å®ƒï¼Œè¯·æ£€æŸ¥åžƒåœ¾é‚®ä»¶/åžƒåœ¾ç®±ã€‚",
                        pulsaRefNoPlaceholder: "å¡«å†™å‘ä»¶äººçš„æ‰‹æœºå·ç æˆ–åºåˆ—å·",
                        transferSuccess: "è½¬ç§»æˆåŠŸ",
                        accountFullNameRequired: "éœ€è¦å…¨å",
                        fullNamesConsistOfAlphabets: "å…¨ååªèƒ½ç”±å­—æ¯å’Œå•ä¸ªç©ºæ ¼ç»„æˆï¼Œä¸å…è®¸å¤šä¸ªè¿žç»­çš„ç©ºæ ¼",
                        minimumThreeCharatersRequired: "è‡³å°‘éœ€è¦3ä¸ªå­—ç¬¦",
                        bankRequired: "éœ€è¦é“¶è¡Œ",
                        bankAccountNumberRequired: "é“¶è¡Œå¸å·å¿…å¡«",
                        bankAccountPattern: "é“¶è¡Œå¸å·åªèƒ½åŒ…å«æ•°å­—",
                        minimumEightLetterRequired: "è‡³å°‘éœ€è¦8ä¸ªå­—ç¬¦",
                        minimum13LetterRequired: "è‡³å°‘éœ€è¦13ä¸ªå­—ç¬¦",
                        minimum10LetterRequired: "è‡³å°‘éœ€è¦10ä¸ªå­—ç¬¦",
                        minimum12LetterRequired: "è‡³å°‘éœ€è¦12ä¸ªå­—ç¬¦",
                        minimum15LetterRequired: "è‡³å°‘éœ€è¦15ä¸ªå­—ç¬¦",
                        maximumTwentycharaters: "æœ€å¤š20ä¸ªå­—ç¬¦",
                        bankAccountNamesNotAvailable: "é“¶è¡Œå¸æˆ·åç§°ä¸å¯ç”¨",
                        success: "æˆåŠŸ",
                        pCodeConfirm: "æ‚¨çš„ä¿ƒé”€ä»£ç å°šæœªç¡®è®¤ã€‚æ‚¨ç¡®å®šè¦ç»§ç»­å—ï¼Ÿ",
                        offlineBank: "é“¶è¡Œç›®å‰å¤„äºŽç¦»çº¿çŠ¶æ€ï¼Œæˆ‘ä»¬å°†åœ¨é“¶è¡Œåœ¨çº¿åŽå¤„ç†æ‚¨çš„äº¤æ˜“ã€‚æ‚¨ç¡®å®šè¦ç»§ç»­å—ï¼Ÿ",
                        walletTranserSuccess: "æˆåŠŸè½¬ç§»åˆ°æ¸¸æˆä¸­",
                        fullNamesConsistAlphabets: "å…¨ååªèƒ½åŒ…å«å­—æ¯",
                        mobileNumberRequired: "æ‰‹æœºå·ç å¿…å¡«",
                        mobileNumberNumbersOnly: "æ‰‹æœºå·ç ä»…éœ€åŒ…å«æ•°å­—",
                        mobileNumberNotAvailable: "æ‰‹æœºå·ç ä¸å¯ç”¨",
                        more: "æ›´å¤š",
                        forgotPassword: "å¿˜è®°å¯†ç ",
                        validatePin: "éªŒè¯å¯†ç "
                    };
                    break;
                case "zh-hk":
                    window.transMsgs = {
                        cfTimeout: "é€£æŽ¥è¶…æ™‚ï¼Œè«‹å˜—è©¦åˆ·æ–°",
                        cfChallenge: "Cloudflare éœ€è¦åœ¨ç¹¼çºŒä¹‹å‰æª¢æŸ¥æ‚¨çš„é€£æŽ¥çš„å®‰å…¨æ€§ã€‚è«‹åˆ·æ–°é é¢",
                        transFailedAmt0: "å°‡å…¨éƒ¨è½‰ç§»åˆ°éŒ¢åŒ…å¤±æ•—ã€‚è½‰è³¬é‡‘é¡å¿…é ˆå¤§æ–¼0",
                        currentPwdRequired: "éœ€è¦ç•¶å‰å¯†ç¢¼",
                        currentPwd5Chars: "ç•¶å‰å¯†ç¢¼å¿…é ˆè¶…éŽ5å€‹å­—ç¬¦",
                        newPwdRequired: "æ–°å¯†ç¢¼ä¸èƒ½ç‚ºç©º",
                        newPwd5Chars: "æ–°å¯†ç¢¼å¿…é ˆè¶…éŽ5å€‹å­—ç¬¦",
                        confirmPwdRequired: "ç¢ºèªå¯†ç¢¼ç‚ºå¿…å¡«é …",
                        confirmPwd5chars: "ç¡®è®¤å¯†ç å¿…é¡»è¶…è¿‡5ä¸ªå­—ç¬¦",
                        confirmPwdNotMatched: "ç¢ºèªå¯†ç¢¼å¿…é ˆè¶…éŽ5å€‹å­—ç¬¦",
                        copied: "è¤‡è£½åˆ°å‰ªè²¼æ¿: ",
                        emailRequired: "é›»å­éƒµä»¶ä¸èƒ½ç‚ºç©º",
                        emailInvalid: "é›»éƒµç„¡æ•ˆ",
                        captchaRequired: "éœ€è¦é©—è­‰ç¢¼",
                        captchaInvalid: "ç„¡æ•ˆè¾“å…¥",
                        minimum4LetterRequired: "è‡³å°‘éœ€è¦4å€‹å­—ç¬¦",
                        userNameRequired: "ç”¨æˆ¶åç‚ºå¿…å¡«é …ï¼Œä¸èƒ½ç‚ºç©º",
                        pwdRequired: "å¯†ç¢¼ç‚ºå¿…å¡«é …ï¼Œä¸èƒ½ç‚ºç©º",
                        plsLogin: "è«‹ç™»éŒ„è¨ªå•æ›´å¤šå…§å®¹",
                        blockedFrGame: "æ‚¨å·²è¢«æš«åœ/è¢«é˜»æ­¢çŽ©æ­¤éŠæˆ²ã€‚è«‹è¯ç¹«å®¢æœä»¥ç²å–æ›´å¤šä¿¡æ¯ã€‚",
                        gameMaintenance: "éŠæˆ²æ­£åœ¨ç¶­è­·ä¸­ã€‚",
                        gameComingSoon: "éŠæˆ²å³å°‡æŽ¨å‡º",
                        pageComingSoon: "è©²é é¢å³å°‡æŽ¨å‡º",
                        gamePromoBlock: "æ‚¨å–®æ“Šçš„éŠæˆ²ä¸å±¬æ–¼ç•¶å‰ä¿ƒéŠ·é¡žåˆ¥ã€‚ä¿ƒéŠ·çµæŸå¾Œï¼Œæ‚¨å¯ä»¥è¿”å›žæ¸¸æˆ²",
                        forgotPwdEmail: "è«‹æª¢æŸ¥æ‚¨çš„é›»å­éƒµä»¶ï¼Œé‡ç½®å¯†ç¢¼é›»å­éƒµä»¶å·²ç™¼é€ã€‚å¦‚æžœæ‚¨æ²’æœ‰åœ¨æ”¶ä»¶ç®±ä¸­æ‰¾åˆ°å®ƒï¼Œè«‹æª¢æŸ¥åžƒåœ¾éƒµä»¶/åžƒåœ¾ç®±ã€‚",
                        pulsaRefNoPlaceholder: "å¡«å¯«ç™¼ä»¶äººçš„æ‰‹æ©Ÿè™Ÿç¢¼æˆ–åºåˆ—è™Ÿ",
                        transferSuccess: "è½‰ç§»æˆåŠŸ",
                        accountFullNameRequired: "éœ€è¦å…¨å",
                        fullNamesConsistOfAlphabets: "å…¨ååªèƒ½ç”±å­—æ¯å’Œå–®å€‹ç©ºæ ¼çµ„æˆï¼Œä¸å…è¨±å¤šå€‹é€£çºŒçš„ç©ºæ ¼",
                        minimumThreeCharatersRequired: "è‡³å°‘éœ€è¦3å€‹å­—ç¬¦",
                        bankRequired: "éœ€è¦éŠ€è¡Œ",
                        bankAccountNumberRequired: "éŠ€è¡Œå¸³è™Ÿå¿…å¡«",
                        bankAccountPattern: "éŠ€è¡Œå¸³è™Ÿåªèƒ½åŒ…å«æ•¸å­—",
                        minimumEightLetterRequired: "è‡³å°‘éœ€è¦8å€‹å­—ç¬¦",
                        minimum13LetterRequired: "è‡³å°‘éœ€è¦13å€‹å­—ç¬¦",
                        minimum10LetterRequired: "è‡³å°‘éœ€è¦10å€‹å­—ç¬¦",
                        minimum12LetterRequired: "è‡³å°‘éœ€è¦12å€‹å­—ç¬¦",
                        minimum15LetterRequired: "è‡³å°‘éœ€è¦15å€‹å­—ç¬¦",
                        maximumTwentycharaters: "æœ€å¤š20å€‹å­—ç¬¦",
                        bankAccountNamesNotAvailable: "éŠ€è¡Œå¸³æˆ¶åç¨±ä¸å¯ç”¨",
                        success: "æˆåŠŸ",
                        pCodeConfirm: "æ‚¨çš„ä¿ƒéŠ·ä»£ç¢¼å°šæœªç¢ºèªã€‚æ‚¨ç¢ºå®šè¦ç¹¼çºŒå—Žï¼Ÿ",
                        offlineBank: "éŠ€è¡Œç›®å‰è™•æ–¼é›¢ç·šç‹€æ…‹ï¼Œæˆ‘å€‘å°‡åœ¨éŠ€è¡Œåœ¨ç·šå¾Œè™•ç†æ‚¨çš„äº¤æ˜“ã€‚æ‚¨ç¢ºå®šè¦ç¹¼çºŒå—Žï¼Ÿ",
                        walletTranserSuccess: "æˆåŠŸè½‰ç§»åˆ°éŠæˆ²ä¸­",
                        fullNamesConsistAlphabets: "å…¨ååªèƒ½åŒ…å«å­—æ¯",
                        mobileNumberRequired: "æ‰‹æ©Ÿè™Ÿç¢¼å¿…å¡«",
                        mobileNumberNumbersOnly: "æ‰‹æ©Ÿè™Ÿç¢¼åƒ…éœ€åŒ…å«æ•¸å­—",
                        mobileNumberNotAvailable: "æ‰‹æ©Ÿè™Ÿç¢¼ä¸å¯ç”¨",
                        more: "æ›´å¤š",
                        forgotPassword: "å¿˜è®°å¯†ç ",
                        validatePin: "éªŒè¯å¯†ç "
                    };
                    break;
                default:
                    window.transMsgs = {
                        cfTimeout: "Connection time out , Please refresh and try again",
                        cfChallenge: "Cloudflare needs to review the security of your connection before proceeding. Please refresh the page",
                        transFailedAmt0: "Transfer ALL to wallet failed. Transfer Amount must be more than 0",
                        currentPwdRequired: "Current Password is required",
                        currentPwd5Chars: "Current Password must be more than 5 characters",
                        newPwdRequired: "New Password is required",
                        newPwd5Chars: "New Password must be more than 5 characters",
                        confirmPwdRequired: "Confirm Password is required",
                        confirmPwd5chars: "Confirm Password must be more than 5 characters",
                        confirmPwdNotMatched: "Confirm Password must match the New Password.",
                        copied: "Copied to clipboard: ",
                        emailRequired: "Email can't be empty",
                        emailInvalid: "Email invalid",
                        captchaRequired: "Captcha Required",
                        captchaInvalid: "Captcha invalid",
                        minimum4LetterRequired: "A minimum of 4 charaters are required",
                        userNameRequired: "Username  is required and can't be empty",
                        pwdRequired: "Password is required and can't be empty",
                        plsLogin: "Please login to continue",
                        blockedFrGame: "You are suspended / blocked from playing this game. Please contact CS for more info.",
                        gameMaintenance: "Game is under maintenance.",
                        gameComingSoon: "Game is coming soon",
                        pageComingSoon: "The Page will coming soon",
                        gamePromoBlock: "The game you clicked does not belong to the current promotion category. After the promotion is finished, you can return to playing",
                        forgotPwdEmail: "Please check your email, the reset password email has been sent. If you did not find it in inbox, kindly check in the spam/junk box.",
                        pulsaRefNoPlaceholder: "Fill in Sender's Mobile Number or Serial Number",
                        transferSuccess: "Transfer success",
                        accountFullNameRequired: "Account Full Name Required",
                        fullNamesConsistOfAlphabets: "Full names can only consist of alphabets and single spaces, multiple consecutive spaces not allowed",
                        minimumThreeCharatersRequired: "A minimum of 3 charaters is required",
                        bankRequired: "Bank Diperlukan",
                        bankAccountNumberRequired: "Bank account number Required",
                        bankAccountPattern: "Bank account numbers can only consist of numbers",
                        minimumEightLetterRequired: "A minimum of 8 charaters are required",
                        minimum13LetterRequired: "A minimum of 13 charaters are required",
                        minimum10LetterRequired: "A minimum of 10 charaters are required",
                        minimum12LetterRequired: "A minimum of 12 charaters are required",
                        minimum15LetterRequired: "A minimum of 15 charaters are required",
                        maximumTwentycharaters: "Maximum of 20 charaters only",
                        bankAccountNamesNotAvailable: "Bank account names are not available",
                        success: "Success",
                        pCodeConfirm: "Your promo code is not yet Your promo code is not yet confirmed. Are you sure you would like to proceed?. Are you sure you would like to proceed?",
                        offlineBank: "BANK IS CURRENTLY OFFLINE , we will process your transaction after Bank Online. Are you sure you would like to proceed?",
                        walletTranserSuccess: "successfully transfered to the game",
                        fullNamesConsistAlphabets: "Full names can only consist of alphabets",
                        mobileNumberRequired: "Mobile number Required",
                        mobileNumberNumbersOnly: "Mobile numbers need to consist of numbers only",
                        mobileNumberNotAvailable: "Mobile number not available",
                        more: "MORE",
                        forgotPassword: "Forgot Password",
                        validatePin: "Validate Pin"
                    }
            }
        },
        946: function () {
            ! function (e) {
                e(document).on("click", "[data-trigger='modal'], [data-trigger='nifty']", (function () {
                    var t = e(this).data("target");
                    return e(t).nifty("show"), !1
                })), e(document).on("click", ".md-overlay", (function (t) {
                    return t.stopPropagation(), e(this).prev().data("isnotcloseoverlay") || e(".nifty-modal.md-show").nifty("hide"), !1
                })), e(document).on("click", ".nifty-modal.md-show .md-close", (function (t) {
                    return t.stopPropagation(), e(this).closest(".nifty-modal.md-show").nifty("hide"), !1
                })), e.fn.extend({
                    nifty: function (t) {
                        var n, a = this,
                            o = "transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd";
                        return "show" == t ? (n = !1, e(a).trigger("show.nifty.modal"), e(a).one(o, (function (t) {
                            n || (n = !0, t.preventDefault(), t.stopPropagation(), e(a).trigger("shown.nifty.modal"))
                        })), e(a).addClass("md-show")) : "hide" == t && function () {
                            var t = !1;
                            e(a).trigger("hide.nifty.modal"), e(a).one(o, (function (n) {
                                t || (t = !0, n.preventDefault(), n.stopPropagation(), e(a).trigger("hidden.nifty.modal"))
                            })), e(a).removeClass("md-show")
                        }(), this
                    }
                })
            }(jQuery)
        },
        401: function () {
            $(document).on("click", ".profile-head .mdc-tab-bar .mdc-tab", (function (e) {
                e.preventDefault(), e.stopPropagation(), $(".profile-head .mdc-tab-bar .mdc-tab").removeClass("active"), $(".profile-head .mdc-tab-bar .mdc-tab .mdc-tab-indicator").removeClass("active"), $(this).addClass("active"), $(this).children(".mdc-tab-indicator").addClass("active");
                var t = $(this).data("tabname");
                switch ($(".profile-head .outlet .tab-pane").html(""), t) {
                    case "edit":
                        xhr_get("/ajaxprofileEdit").done((function (e) {
                            $(".profile-head .outlet .tab-pane").html(e)
                        }));
                        break;
                    case "change-password":
                        xhr_get("/ajaxchgPass").done((function (e) {
                            $(".profile-head .outlet .tab-pane").html(e)
                        }));
                        break;
                    case "my-bonus":
                        xhr_get("/ajaxmyBonus").done((function (e) {
                            $(".profile-head .outlet .tab-pane").html(e)
                        }));
                        break;
                    case "my-promo":
                        xhr_get("/ajaxmyPromo").done((function (e) {
                            $(".profile-head .outlet .tab-pane").html(e)
                        }))
                }
                return !1
            }))
        },
        431: function () {
            $(document).on("click", "#right-button", (function () {
                return event.preventDefault(), $(".slider-content").animate({
                    scrollLeft: "+=300px"
                }, "fast"), !1
            })), $(document).on("click", "#left-button", (function () {
                return event.preventDefault(), $(".slider-content").animate({
                    scrollLeft: "-=300px"
                }, "fast"), !1
            })), $(window).bind("load", (function () {
                var e = $(".popular-game-slider").find("ul"),
                    t = e.width(),
                    n = 1;
                setInterval((function () {
                    n >= e.data("count") ? (n = 1, e.animate({
                        scrollLeft: "-=" + e.data("count") * t + "px"
                    }, "fast")) : (n++, e.animate({
                        scrollLeft: "+=" + t + "px"
                    }, "fast"))
                }), 1500), $(".g-slider-wrapper .js-btn-next").on("click", (function () {
                    n++;
                    var e = $(this).parents(".g-slider-wrapper").find("ul"),
                        t = e.width();
                    return e.animate({
                        scrollLeft: "+=" + t + "px"
                    }, "fast"), !1
                })), $(".g-slider-wrapper .js-btn-prev").on("click", (function () {
                    n--;
                    var e = $(this).parents(".g-slider-wrapper").find("ul"),
                        t = e.width();
                    return e.animate({
                        scrollLeft: "-=" + t + "px"
                    }, "fast"), !1
                }))
            })), $((function () {
                var e, t, n = $(".js-cycling-gallery ul"),
                    a = n.find("li"),
                    o = n.data("count"),
                    i = 1,
                    r = -1 * a.width(),
                    s = r * o,
                    l = !1,
                    c = 0,
                    u = 0;
                if (n.length > 0) {
                    var d = $(".js-cycling-btn");
                    n.css({
                        left: s
                    }), console.log("gallery startPos", s), d.on("click", (function () {
                        f($(this).data("action"), 5)
                    })), setInterval((function () {
                        l || f("next", 1)
                    }), 1300), n.on("touchstart", (function (t) {
                        l = !0, (t = t || window.event).preventDefault(), e = parseFloat(n.css("left")), "touchstart" == t.type ? c = t.touches[0].clientX : (c = t.clientX, document.onmouseup = h, document.onmousemove = m)
                    })), n.on("touchend", h), n.on("touchmove", m)
                }

                function m(e) {
                    e = e || window.event, l = !0, "touchmove" == e.type ? (u = c - e.touches[0].clientX, c = e.touches[0].clientX) : (u = c - e.clientX, c = e.clientX), n.css({
                        left: parseFloat(n.css("left")) - u + "px"
                    })
                }

                function h(a) {
                    t = parseFloat(n.css("left"));
                    var o = Math.round((t - e) / r);
                    o <= -1 ? f("next", Math.abs(o)) : o >= 1 ? f("next", o) : n.css({
                        left: e + "px"
                    }), document.onmouseup = null, document.onmousemove = null, l = !1
                }

                function f(e, t) {
                    t || (t = 1);
                    var a = "prev" === e ? -t : t;
                    n.animate({
                        left: "+=" + r * a
                    }, (function () {
                        !!(0 === (i += a) || i > o) && (i = 0 === i ? o : 1, n.css({
                            left: r * (i + 1)
                        }))
                    }))
                }
            })), $((function () {
                var e, t, n = $(".js-cycling-gallery-royal-hotgame ul"),
                    a = n.find("li"),
                    o = n.data("count") - 3,
                    i = 1,
                    r = -1 * a.width(),
                    s = !1,
                    l = 0,
                    c = 0;
                if (n.length > 0) {
                    var u = $(".js-cycling-btn");
                    n.css({
                        left: 0
                    }), console.log("gallery startPos", 0), u.on("click", (function () {
                        h($(this).data("action"), 5)
                    })), setInterval((function () {
                        s || h("next", 1)
                    }), 3300), n.on("touchstart", (function (t) {
                        s = !0, (t = t || window.event).preventDefault(), e = parseFloat(n.css("left")), "touchstart" == t.type ? l = t.touches[0].clientX : (l = t.clientX, document.onmouseup = m, document.onmousemove = d)
                    })), n.on("touchend", m), n.on("touchmove", d)
                }

                function d(e) {
                    e = e || window.event, s = !0, "touchmove" == e.type ? (c = l - e.touches[0].clientX, l = e.touches[0].clientX) : (c = l - e.clientX, l = e.clientX), n.css({
                        left: parseFloat(n.css("left")) - c + "px"
                    })
                }

                function m(a) {
                    t = parseFloat(n.css("left"));
                    var o = Math.round((t - e) / r);
                    o <= -1 ? h("next", Math.abs(o)) : o >= 1 ? h("next", o) : n.css({
                        left: e + "px"
                    }), document.onmouseup = null, document.onmousemove = null, s = !1
                }

                function h(e, t) {
                    t || (t = 1);
                    var a = "prev" === e ? -t : t;
                    n.animate({
                        left: "+=" + r * a
                    }, (function () {
                        !!(0 === (i += a) || i > o) && (i = 0 === i ? o : 1, n.css({
                            left: r * (i + 1)
                        }))
                    }))
                }
            })), $((function () {
                var e, t, n = $(".js-cycling-gallery-neo ul"),
                    a = n.find("li"),
                    o = n.data("count"),
                    i = 1,
                    r = -1 * a.width(),
                    s = r * o,
                    l = !1,
                    c = 0,
                    u = 0;
                if (n.length > 0) {
                    var d = $(".js-cycling-btn");
                    n.css({
                        left: s
                    }), console.log("gallery startPos 22", s, o), d.on("click", (function () {
                        f($(this).data("action"), 1)
                    })), setInterval((function () {
                        l || f("next", 1)
                    }), 2e3), n.on("touchstart", (function (t) {
                        l = !0, (t = t || window.event).preventDefault(), e = parseFloat(n.css("left")), "touchstart" == t.type ? c = t.touches[0].clientX : (c = t.clientX, document.onmouseup = h, document.onmousemove = m)
                    })), n.on("touchend", h), n.on("touchmove", m)
                }

                function m(e) {
                    e = e || window.event, l = !0, "touchmove" == e.type ? (u = c - e.touches[0].clientX, c = e.touches[0].clientX) : (u = c - e.clientX, c = e.clientX), n.css({
                        left: parseFloat(n.css("left")) - u + "px"
                    })
                }

                function h(a) {
                    t = parseFloat(n.css("left"));
                    var o = Math.round((t - e) / r);
                    o <= -1 ? f("next", Math.abs(o)) : o >= 1 ? f("next", o) : n.css({
                        left: e + "px"
                    }), document.onmouseup = null, document.onmousemove = null, l = !1
                }

                function f(e, t) {
                    t || (t = 1);
                    var a = "prev" === e ? -t : t;
                    console.log("delta" + a), n.animate({
                        left: "+=" + (r - 27) * a
                    }, (function () {
                        !!(0 === (i += a) || i > o) && (i = 0 === i ? o : 1, n.css({
                            left: (r - 27) * (i + 1)
                        }))
                    }))
                }
            })), $((function () {
                var e, t, n = $(".js-cycling-widthdraw-horizontal ul"),
                    a = n.find("li"),
                    o = n.data("count"),
                    i = 1,
                    r = -1 * a.width(),
                    s = r * o,
                    l = !1,
                    c = 0,
                    u = 0;
                if (n.length > 0) {
                    var d = $(".js-cycling-btn");
                    n.css({
                        left: s
                    }), console.log("gallery startPos", s), d.on("click", (function () {
                        f($(this).data("action"), 5)
                    })), setInterval((function () {
                        l || f("next", 1)
                    }), 1300), n.on("touchstart", (function (t) {
                        l = !0, (t = t || window.event).preventDefault(), e = parseFloat(n.css("left")), "touchstart" == t.type ? c = t.touches[0].clientX : (c = t.clientX, document.onmouseup = h, document.onmousemove = m)
                    })), n.on("touchend", h), n.on("touchmove", m)
                }

                function m(e) {
                    e = e || window.event, l = !0, "touchmove" == e.type ? (u = c - e.touches[0].clientX, c = e.touches[0].clientX) : (u = c - e.clientX, c = e.clientX), n.css({
                        left: parseFloat(n.css("left")) - u + "px"
                    })
                }

                function h(a) {
                    t = parseFloat(n.css("left"));
                    var o = Math.round((t - e) / r);
                    o <= -1 ? f("next", Math.abs(o)) : o >= 1 ? f("next", o) : n.css({
                        left: e + "px"
                    }), document.onmouseup = null, document.onmousemove = null, l = !1
                }

                function f(e, t) {
                    t || (t = 1);
                    var a = "prev" === e ? -t : t;
                    n.animate({
                        left: "+=" + r * a
                    }, (function () {
                        !!(0 === (i += a) || i > o) && (i = 0 === i ? o : 1, n.css({
                            left: r * (i + 1)
                        }))
                    }))
                }
            })), $((function () {
                var e, t, n = $(".js-cycling-widthdraw ul"),
                    a = n.find("li"),
                    o = n.data("count"),
                    i = 1,
                    r = -1 * a.height(),
                    s = r * o,
                    l = !1,
                    c = 0,
                    u = 0;
                if (n.length > 0) {
                    var d = $(".js-cycling-btn");
                    n.css({
                        top: s
                    }), console.log("widthdraw startPos", s), d.on("click", (function () {
                        f($(this).data("action"), 5)
                    })), setInterval((function () {
                        l || f("next", 1)
                    }), 1300), n.on("touchstart", (function (t) {
                        l = !0, (t = t || window.event).preventDefault(), e = parseFloat(n.css("top")), "touchstart" == t.type ? c = t.touches[0].clientX : (c = t.clientX, document.onmouseup = h, document.onmousemove = m)
                    })), n.on("touchend", h), n.on("touchmove", m)
                }

                function m(e) {
                    e = e || window.event, l = !0, "touchmove" == e.type ? (u = c - e.touches[0].clientX, c = e.touches[0].clientX) : (u = c - e.clientX, c = e.clientX), n.css({
                        top: parseFloat(n.css("top")) - u + "px"
                    })
                }

                function h(a) {
                    t = parseFloat(n.css("top"));
                    var o = Math.round((t - e) / r);
                    o <= -1 ? f("next", Math.abs(o)) : o >= 1 ? f("next", o) : n.css({
                        top: e + "px"
                    }), document.onmouseup = null, document.onmousemove = null, l = !1
                }

                function f(e, t) {
                    t || (t = 1);
                    var a = "prev" === e ? -t : t;
                    n.animate({
                        top: "+=" + r * a
                    }, (function () {
                        !!(0 === (i += a) || i > o) && (i = 0 === i ? o : 1, n.css({
                            top: r * (i + 1)
                        }))
                    }))
                }
            })), $((function () {
                var e, t, n = $(".js-cycling-deposit ul"),
                    a = n.find("li"),
                    o = n.data("count"),
                    i = 1,
                    r = -1 * a.height(),
                    s = r * o,
                    l = !1,
                    c = 0,
                    u = 0;
                if (n.length > 0) {
                    var d = $(".js-cycling-btn");
                    n.css({
                        top: s
                    }), console.log("deposit startPos", s), d.on("click", (function () {
                        f($(this).data("action"), 5)
                    })), setInterval((function () {
                        l || f("next", 1)
                    }), 1300), n.on("touchstart", (function (t) {
                        l = !0, (t = t || window.event).preventDefault(), e = parseFloat(n.css("top")), "touchstart" == t.type ? c = t.touches[0].clientX : (c = t.clientX, document.onmouseup = h, document.onmousemove = m)
                    })), n.on("touchend", h), n.on("touchmove", m)
                }

                function m(e) {
                    e = e || window.event, l = !0, "touchmove" == e.type ? (u = c - e.touches[0].clientX, c = e.touches[0].clientX) : (u = c - e.clientX, c = e.clientX), n.css({
                        top: parseFloat(n.css("top")) - u + "px"
                    })
                }

                function h(a) {
                    t = parseFloat(n.css("top"));
                    var o = Math.round((t - e) / r);
                    o <= -1 ? f("next", Math.abs(o)) : o >= 1 ? f("next", o) : n.css({
                        top: e + "px"
                    }), document.onmouseup = null, document.onmousemove = null, l = !1
                }

                function f(e, t) {
                    t || (t = 1);
                    var a = "prev" === e ? -t : t;
                    n.animate({
                        top: "+=" + r * a
                    }, (function () {
                        !!(0 === (i += a) || i > o) && (i = 0 === i ? o : 1, n.css({
                            top: r * (i + 1)
                        }))
                    }))
                }
            })), $(window).bind("load", (function () {
                $(".js-cycling-gallery-ams").each((function (e, t) {
                    var n = $(this).find("ul"),
                        a = n.find("li"),
                        o = n.data("count"),
                        i = 1,
                        r = -1 * a.width(),
                        s = !1;
                    console.log("item li width ", a.width());
                    var l, c, u = 0,
                        d = 0;
                    if (n.length > 0) {
                        var m = $(this).find(".js-cycling-btn");
                        n.css({
                            left: 0
                        }), console.log("gallery startPos 22", 0, o), m.on("click", (function () {
                            p($(this).data("action"), 1)
                        })), setInterval((function () {
                            s || p("next", 1)
                        }), 2500), n.on("touchstart", (function (e) {
                            s = !0, e = e || window.event, l = parseFloat(n.css("left")), "touchstart" == e.type ? u = e.touches[0].clientX : (u = e.clientX, document.onmouseup = f, document.onmousemove = h)
                        })), n.on("touchend", f), n.on("touchmove", h)
                    }

                    function h(e) {
                        e = e || window.event, s = !0, "touchmove" == e.type ? (d = u - e.touches[0].clientX, u = e.touches[0].clientX) : (d = u - e.clientX, u = e.clientX), n.css({
                            left: parseFloat(n.css("left")) - d + "px"
                        })
                    }

                    function f(e) {
                        c = parseFloat(n.css("left"));
                        var t = Math.round((c - l) / r);
                        t <= -1 ? p("next", Math.abs(t)) : t >= 1 ? p("next", t) : n.css({
                            left: l + "px"
                        }), document.onmouseup = null, document.onmousemove = null, s = !1
                    }

                    function p(e, t) {
                        t || (t = 1);
                        var a = "prev" === e ? -t : t;
                        n.animate({
                            left: "+=" + (r - 27) * a
                        }, (function () {
                            !!(0 === (i += a) || i > o + 1) && (i = 0 === i ? o : 1, n.css({
                                left: 0
                            }))
                        }))
                    }
                }))
            })), $(window).bind("load", (function () {
                $(".js-cycling-gallery-onix").each((function (e, t) {
                    var n = $(this).find("ul"),
                        a = n.find("li"),
                        o = n.data("count"),
                        i = 1,
                        r = -1 * a.width(),
                        s = !1;
                    console.log("item li width ", a.width());
                    var l, c, u = 0,
                        d = 0;
                    if (n.length > 0) {
                        var m = $(this).find(".js-cycling-btn");
                        n.css({
                            left: 0
                        }), console.log("gallery startPos 10", 0, o), m.on("click", (function () {
                            p($(this).data("action"), 1)
                        })), setInterval((function () {
                            s || p("next", 1)
                        }), 2500), n.on("touchstart", (function (e) {
                            s = !0, e = e || window.event, l = parseFloat(n.css("left")), "touchstart" == e.type ? u = e.touches[0].clientX : (u = e.clientX, document.onmouseup = f, document.onmousemove = h)
                        })), n.on("touchend", f), n.on("touchmove", h)
                    }

                    function h(e) {
                        e = e || window.event, s = !0, "touchmove" == e.type ? (d = u - e.touches[0].clientX, u = e.touches[0].clientX) : (d = u - e.clientX, u = e.clientX), n.css({
                            left: parseFloat(n.css("left")) - d + "px"
                        })
                    }

                    function f(e) {
                        c = parseFloat(n.css("left"));
                        var t = Math.round((c - l) / r);
                        t <= -1 ? p("next", Math.abs(t)) : t >= 1 ? p("next", t) : n.css({
                            left: l + "px"
                        }), document.onmouseup = null, document.onmousemove = null, s = !1
                    }

                    function p(e, t) {
                        t || (t = 1);
                        var a = "prev" === e ? -t : t;
                        n.animate({
                            left: "+=" + (r - 2) * a
                        }, (function () {
                            !!(0 === (i += a) || i > o + 1) && (i = 0 === i ? o : 1, n.css({
                                left: (r - 2) * (i + 1)
                            }))
                        }))
                    }
                }))
            })), $(window).bind("load", (function () {
                $(".js-cycling-gallery-royal").each((function (e, t) {
                    var n = $(this).find("ul"),
                        a = n.find("li"),
                        o = n.data("count"),
                        i = 1,
                        r = -1 * a.width(),
                        s = !1;
                    console.log("item li width ", a.width());
                    var l, c, u = 0,
                        d = 0;
                    if (n.length > 0) {
                        var m = $(this).find(".js-cycling-btn");
                        n.css({
                            left: 0
                        }), console.log("gallery startPos 22", 0, o), m.on("click", (function () {
                            p($(this).data("action"), 1)
                        })), setInterval((function () {
                            s || p("next", 1)
                        }), 4e3), n.on("touchstart", (function (e) {
                            s = !0, e = e || window.event, l = parseFloat(n.css("left")), "touchstart" == e.type ? u = e.touches[0].clientX : (u = e.clientX, document.onmouseup = f, document.onmousemove = h)
                        })), n.on("touchend", f), n.on("touchmove", h)
                    }

                    function h(e) {
                        e = e || window.event, s = !0, "touchmove" == e.type ? (d = u - e.touches[0].clientX, u = e.touches[0].clientX) : (d = u - e.clientX, u = e.clientX), n.css({
                            left: parseFloat(n.css("left")) - d + "px"
                        })
                    }

                    function f(e) {
                        c = parseFloat(n.css("left"));
                        var t = Math.round((c - l) / r);
                        t <= -1 ? p("next", Math.abs(t)) : t >= 1 ? p("next", t) : n.css({
                            left: l + "px"
                        }), document.onmouseup = null, document.onmousemove = null, s = !1
                    }

                    function p(e, t) {
                        t || (t = 1);
                        var a = "prev" === e ? -t : t;
                        n.animate({
                            left: "+=" + (r - 27) * a
                        }, (function () {
                            !!(0 === (i += a) || i > o + 1) && (i = 0 === i ? o : 1, n.css({
                                left: 0
                            }))
                        }))
                    }
                }))
            })), $(window).bind("load", (function () {
                var e = $(".js-cycling-gallery-olympus .custom-slider"),
                    t = e.find("ul"),
                    n = e.data("count"),
                    a = 1,
                    o = -1 * t.width(),
                    i = !1;
                console.log("item li width ", t.width());
                var r, s, l = 0,
                    c = 0;
                if (e.length > 0) {
                    var u = $(".js-cycling-btn");
                    e.css({
                        left: 0
                    }), console.log("gallery startPos 22", 0, n), u.on("click", (function () {
                        h($(this).data("action"), 1)
                    })), setInterval((function () {
                        i || h("next", 1)
                    }), 2500), e.on("touchstart", (function (t) {
                        i = !0, t = t || window.event, r = parseFloat(e.css("left")), "touchstart" == t.type ? l = t.touches[0].clientX : (l = t.clientX, document.onmouseup = m, document.onmousemove = d)
                    })), e.on("touchend", m), e.on("touchmove", d)
                }

                function d(t) {
                    t = t || window.event, i = !0, "touchmove" == t.type ? (c = l - t.touches[0].clientX, l = t.touches[0].clientX) : (c = l - t.clientX, l = t.clientX), e.css({
                        left: parseFloat(e.css("left")) - c + "px"
                    })
                }

                function m(t) {
                    s = parseFloat(e.css("left"));
                    var n = Math.round((s - r) / o);
                    n <= -1 ? h("next", Math.abs(n)) : n >= 1 ? h("next", n) : e.css({
                        left: r + "px"
                    }), document.onmouseup = null, document.onmousemove = null, i = !1
                }

                function h(t, i) {
                    i || (i = 1);
                    var r = "prev" === t ? -i : i;
                    e.animate({
                        left: "+=" + (o - 27) * r
                    }, (function () {
                        !!(0 === (a += r) || a > n + 1) && (a = 0 === a ? n : 1, e.css({
                            left: 0
                        }))
                    }))
                }
            })), $((function () {
                var e = $(".trx-slider-x ul"),
                    t = e.find("li"),
                    n = e.data("count"),
                    a = 1,
                    o = -1 * (t.width() + 6);
                if (e.length > 0) {
                    var i = $(".js-cycling-btn");
                    e.css({
                        left: 0
                    }), console.log("gallery startPos", o, 0, n), i.on("click", (function () {
                        r($(this).data("action"), 1)
                    })), setInterval((function () {
                        r("next", 1)
                    }), 2e3)
                }

                function r(t, i) {
                    i || (i = 1);
                    var r = "prev" === t ? -i : i;
                    console.log("delta" + r), e.animate({
                        left: "+=" + (o - 27) * r
                    }, (function () {
                        !!(0 === (a += r) || a > n) && (a = 0 === a ? n : 1, e.css({
                            left: (o - 30) * a
                        }))
                    }))
                }
            })), $((function () {
                var e = $(".trx-slider-y ul"),
                    t = e.find("li"),
                    n = e.data("count"),
                    a = 1,
                    o = -1 * (t.height() + 31);
                if (e.length > 0) {
                    var i = $(".js-cycling-btn");
                    e.css({
                        top: 0
                    }), console.log("widthdraw startPos", 0, t.height(), o), i.on("click", (function () {
                        r($(this).data("action"), 5)
                    })), setInterval((function () {
                        r("next", 1)
                    }), 1300)
                }

                function r(t, i) {
                    i || (i = 1);
                    var r = "prev" === t ? -i : i;
                    e.animate({
                        top: "+=" + o * r
                    }, (function () {
                        !!(0 === (a += r) || a > n) && (a = 0 === a ? n : 1, e.css({
                            top: (o + 34) * (a + 1)
                        }))
                    }))
                }
            })), $((function () {
                var e = $(".js-cycling-gallery-giga ul"),
                    t = e.find("li"),
                    n = e.data("count"),
                    a = 1,
                    o = $(window).width() < 600 ? -1 * (t.width() - 10) : -1 * (t.width() + 15);
                startPos = o * n, pauseAnimation = !1, console.log("gallery width", t.width());
                var i, r, s = 0,
                    l = 0;
                if (e.length > 0) {
                    var c = $(".js-cycling-btn");
                    e.css({
                        left: startPos
                    }), console.log("gallery startPos 22", startPos, n), c.on("click", (function () {
                        m($(this).data("action"), 1)
                    })), setInterval((function () {
                        pauseAnimation || m("next", 1)
                    }), 2e3), e.on("touchstart", (function (t) {
                        pauseAnimation = !0, (t = t || window.event).preventDefault(), i = parseFloat(e.css("left")), "touchstart" == t.type ? s = t.touches[0].clientX : (s = t.clientX, document.onmouseup = d, document.onmousemove = u)
                    })), e.on("touchend", d), e.on("touchmove", u)
                }

                function u(t) {
                    t = t || window.event, pauseAnimation = !0, "touchmove" == t.type ? (l = s - t.touches[0].clientX, s = t.touches[0].clientX) : (l = s - t.clientX, s = t.clientX), e.css({
                        left: parseFloat(e.css("left")) - l + "px"
                    })
                }

                function d(t) {
                    r = parseFloat(e.css("left"));
                    var n = Math.round((r - i) / o);
                    n <= -1 ? m("next", Math.abs(n)) : n >= 1 ? m("next", n) : e.css({
                        left: i + "px"
                    }), document.onmouseup = null, document.onmousemove = null, pauseAnimation = !1
                }

                function m(t, i) {
                    i || (i = 1);
                    var r = "prev" === t ? -i : i;
                    console.log("delta" + r), e.animate({
                        left: "+=" + (o - 27) * r
                    }, (function () {
                        !!(0 === (a += r) || a > n) && (a = 0 === a ? n : 1, e.css({
                            left: (o - 27) * (a + 1)
                        }))
                    }))
                }
            }))
        },
        90: function (e) {
            ! function (t, n) {
                var a = function (e, t, n) {
                    "use strict";
                    var a, o;
                    if (function () {
                        var t, n = {
                            lazyClass: "lazyload",
                            loadedClass: "lazyloaded",
                            loadingClass: "lazyloading",
                            preloadClass: "lazypreload",
                            errorClass: "lazyerror",
                            autosizesClass: "lazyautosizes",
                            fastLoadedClass: "ls-is-cached",
                            iframeLoadMode: 0,
                            srcAttr: "data-src",
                            srcsetAttr: "data-srcset",
                            sizesAttr: "data-sizes",
                            minSize: 40,
                            customMedia: {},
                            init: !0,
                            expFactor: 1.5,
                            hFac: .8,
                            loadMode: 2,
                            loadHidden: !0,
                            ricTimeout: 0,
                            throttleDelay: 125
                        };
                        for (t in o = e.lazySizesConfig || e.lazysizesConfig || {}, n) t in o || (o[t] = n[t])
                    }(), !t || !t.getElementsByClassName) return {
                        init: function () { },
                        cfg: o,
                        noSupport: !0
                    };
                    var i = t.documentElement,
                        r = e.HTMLPictureElement,
                        s = "addEventListener",
                        l = "getAttribute",
                        c = e[s].bind(e),
                        u = e.setTimeout,
                        d = e.requestAnimationFrame || u,
                        m = e.requestIdleCallback,
                        h = /^picture$/i,
                        f = ["load", "error", "lazyincluded", "_lazyloaded"],
                        p = {},
                        g = Array.prototype.forEach,
                        b = function (e, t) {
                            return p[t] || (p[t] = new RegExp("(\\s|^)" + t + "(\\s|$)")), p[t].test(e[l]("class") || "") && p[t]
                        },
                        v = function (e, t) {
                            b(e, t) || e.setAttribute("class", (e[l]("class") || "").trim() + " " + t)
                        },
                        w = function (e, t) {
                            var n;
                            (n = b(e, t)) && e.setAttribute("class", (e[l]("class") || "").replace(n, " "))
                        },
                        $ = function (e, t, n) {
                            var a = n ? s : "removeEventListener";
                            n && $(e, t), f.forEach((function (n) {
                                e[a](n, t)
                            }))
                        },
                        k = function (e, n, o, i, r) {
                            var s = t.createEvent("Event");
                            return o || (o = {}), o.instance = a, s.initEvent(n, !i, !r), s.detail = o, e.dispatchEvent(s), s
                        },
                        y = function (t, n) {
                            var a;
                            !r && (a = e.picturefill || o.pf) ? (n && n.src && !t[l]("srcset") && t.setAttribute("srcset", n.src), a({
                                reevaluate: !0,
                                elements: [t]
                            })) : n && n.src && (t.src = n.src)
                        },
                        C = function (e, t) {
                            return (getComputedStyle(e, null) || {})[t]
                        },
                        x = function (e, t, n) {
                            for (n = n || e.offsetWidth; n < o.minSize && t && !e._lazysizesWidth;) n = t.offsetWidth, t = t.parentNode;
                            return n
                        },
                        _ = (ve = [], we = [], $e = ve, ke = function () {
                            var e = $e;
                            for ($e = ve.length ? we : ve, ge = !0, be = !1; e.length;) e.shift()();
                            ge = !1
                        }, ye = function (e, n) {
                            ge && !n ? e.apply(this, arguments) : ($e.push(e), be || (be = !0, (t.hidden ? u : d)(ke)))
                        }, ye._lsFlush = ke, ye),
                        P = function (e, t) {
                            return t ? function () {
                                _(e)
                            } : function () {
                                var t = this,
                                    n = arguments;
                                _((function () {
                                    e.apply(t, n)
                                }))
                            }
                        },
                        q = function (e) {
                            var t, a = 0,
                                i = o.throttleDelay,
                                r = o.ricTimeout,
                                s = function () {
                                    t = !1, a = n.now(), e()
                                },
                                l = m && r > 49 ? function () {
                                    m(s, {
                                        timeout: r
                                    }), r !== o.ricTimeout && (r = o.ricTimeout)
                                } : P((function () {
                                    u(s)
                                }), !0);
                            return function (e) {
                                var o;
                                (e = !0 === e) && (r = 33), t || (t = !0, (o = i - (n.now() - a)) < 0 && (o = 0), e || o < 9 ? l() : u(l, o))
                            }
                        },
                        A = function (e) {
                            var t, a, o = 99,
                                i = function () {
                                    t = null, e()
                                },
                                r = function () {
                                    var e = n.now() - a;
                                    e < o ? u(r, o - e) : (m || i)(i)
                                };
                            return function () {
                                a = n.now(), t || (t = u(r, o))
                            }
                        },
                        N = (G = /^img$/i, J = /^iframe$/i, Y = "onscroll" in e && !/(gle|ing)bot/.test(navigator.userAgent), V = 0, Q = 0, Z = 0, ee = -1, te = function (e) {
                            Z--, (!e || Z < 0 || !e.target) && (Z = 0)
                        }, ne = function (e) {
                            return null == U && (U = "hidden" == C(t.body, "visibility")), U || !("hidden" == C(e.parentNode, "visibility") && "hidden" == C(e, "visibility"))
                        }, ae = function (e, n) {
                            var a, o = e,
                                r = ne(e);
                            for (O -= n, K += n, H -= n, W += n; r && (o = o.offsetParent) && o != t.body && o != i;)(r = (C(o, "opacity") || 1) > 0) && "visible" != C(o, "overflow") && (a = o.getBoundingClientRect(), r = W > a.left && H < a.right && K > a.top - 1 && O < a.bottom + 1);
                            return r
                        }, oe = function () {
                            var e, n, r, s, c, u, d, m, h, f, p, g, b = a.elements;
                            if ((E = o.loadMode) && Z < 8 && (e = b.length)) {
                                for (n = 0, ee++; n < e; n++)
                                    if (b[n] && !b[n]._lazyRace)
                                        if (!Y || a.prematureUnveil && a.prematureUnveil(b[n])) me(b[n]);
                                        else if ((m = b[n][l]("data-expand")) && (u = 1 * m) || (u = Q), f || (f = !o.expand || o.expand < 1 ? i.clientHeight > 500 && i.clientWidth > 500 ? 500 : 370 : o.expand, a._defEx = f, p = f * o.expFactor, g = o.hFac, U = null, Q < p && Z < 1 && ee > 2 && E > 2 && !t.hidden ? (Q = p, ee = 0) : Q = E > 1 && ee > 1 && Z < 6 ? f : V), h !== u && (B = innerWidth + u * g, X = innerHeight + u, d = -1 * u, h = u), r = b[n].getBoundingClientRect(), (K = r.bottom) >= d && (O = r.top) <= X && (W = r.right) >= d * g && (H = r.left) <= B && (K || W || H || O) && (o.loadHidden || ne(b[n])) && (I && Z < 3 && !m && (E < 3 || ee < 4) || ae(b[n], u))) {
                                            if (me(b[n]), c = !0, Z > 9) break
                                        } else !c && I && !s && Z < 4 && ee < 4 && E > 2 && (j[0] || o.preloadAfterLoad) && (j[0] || !m && (K || W || H || O || "auto" != b[n][l](o.sizesAttr))) && (s = j[0] || b[n]);
                                s && !c && me(s)
                            }
                        }, ie = q(oe), re = function (e) {
                            var t = e.target;
                            t._lazyCache ? delete t._lazyCache : (te(e), v(t, o.loadedClass), w(t, o.loadingClass), $(t, le), k(t, "lazyloaded"))
                        }, se = P(re), le = function (e) {
                            se({
                                target: e.target
                            })
                        }, ce = function (e, t) {
                            var n = e.getAttribute("data-load-mode") || o.iframeLoadMode;
                            0 == n ? e.contentWindow.location.replace(t) : 1 == n && (e.src = t)
                        }, ue = function (e) {
                            var t, n = e[l](o.srcsetAttr);
                            (t = o.customMedia[e[l]("data-media") || e[l]("media")]) && e.setAttribute("media", t), n && e.setAttribute("srcset", n)
                        }, de = P((function (e, t, n, a, i) {
                            var r, s, c, d, m, f;
                            (m = k(e, "lazybeforeunveil", t)).defaultPrevented || (a && (n ? v(e, o.autosizesClass) : e.setAttribute("sizes", a)), s = e[l](o.srcsetAttr), r = e[l](o.srcAttr), i && (d = (c = e.parentNode) && h.test(c.nodeName || "")), f = t.firesLoad || "src" in e && (s || r || d), m = {
                                target: e
                            }, v(e, o.loadingClass), f && (clearTimeout(z), z = u(te, 2500), $(e, le, !0)), d && g.call(c.getElementsByTagName("source"), ue), s ? e.setAttribute("srcset", s) : r && !d && (J.test(e.nodeName) ? ce(e, r) : e.src = r), i && (s || d) && y(e, {
                                src: r
                            })), e._lazyRace && delete e._lazyRace, w(e, o.lazyClass), _((function () {
                                var t = e.complete && e.naturalWidth > 1;
                                f && !t || (t && v(e, o.fastLoadedClass), re(m), e._lazyCache = !0, u((function () {
                                    "_lazyCache" in e && delete e._lazyCache
                                }), 9)), "lazy" == e.loading && Z--
                            }), !0)
                        })), me = function (e) {
                            if (!e._lazyRace) {
                                var t, n = G.test(e.nodeName),
                                    a = n && (e[l](o.sizesAttr) || e[l]("sizes")),
                                    i = "auto" == a;
                                (!i && I || !n || !e[l]("src") && !e.srcset || e.complete || b(e, o.errorClass) || !b(e, o.lazyClass)) && (t = k(e, "lazyunveilread").detail, i && R.updateElem(e, !0, e.offsetWidth), e._lazyRace = !0, Z++, de(e, t, i, a, n))
                            }
                        }, he = A((function () {
                            o.loadMode = 3, ie()
                        })), fe = function () {
                            3 == o.loadMode && (o.loadMode = 2), he()
                        }, pe = function () {
                            I || (n.now() - D < 999 ? u(pe, 999) : (I = !0, o.loadMode = 3, ie(), c("scroll", fe, !0)))
                        }, {
                            _: function () {
                                D = n.now(), a.elements = t.getElementsByClassName(o.lazyClass), j = t.getElementsByClassName(o.lazyClass + " " + o.preloadClass), c("scroll", ie, !0), c("resize", ie, !0), c("pageshow", (function (e) {
                                    if (e.persisted) {
                                        var n = t.querySelectorAll("." + o.loadingClass);
                                        n.length && n.forEach && d((function () {
                                            n.forEach((function (e) {
                                                e.complete && me(e)
                                            }))
                                        }))
                                    }
                                })), e.MutationObserver ? new MutationObserver(ie).observe(i, {
                                    childList: !0,
                                    subtree: !0,
                                    attributes: !0
                                }) : (i[s]("DOMNodeInserted", ie, !0), i[s]("DOMAttrModified", ie, !0), setInterval(ie, 999)), c("hashchange", ie, !0), ["focus", "mouseover", "click", "load", "transitionend", "animationend"].forEach((function (e) {
                                    t[s](e, ie, !0)
                                })), /d$|^c/.test(t.readyState) ? pe() : (c("load", pe), t[s]("DOMContentLoaded", ie), u(pe, 2e4)), a.elements.length ? (oe(), _._lsFlush()) : ie()
                            },
                            checkElems: ie,
                            unveil: me,
                            _aLSL: fe
                        }),
                        R = (T = P((function (e, t, n, a) {
                            var o, i, r;
                            if (e._lazysizesWidth = a, a += "px", e.setAttribute("sizes", a), h.test(t.nodeName || ""))
                                for (i = 0, r = (o = t.getElementsByTagName("source")).length; i < r; i++) o[i].setAttribute("sizes", a);
                            n.detail.dataAttr || y(e, n.detail)
                        })), S = function (e, t, n) {
                            var a, o = e.parentNode;
                            o && (n = x(e, o, n), (a = k(e, "lazybeforesizes", {
                                width: n,
                                dataAttr: !!t
                            })).defaultPrevented || (n = a.detail.width) && n !== e._lazysizesWidth && T(e, o, a, n))
                        }, L = A((function () {
                            var e, t = F.length;
                            if (t)
                                for (e = 0; e < t; e++) S(F[e])
                        })), {
                            _: function () {
                                F = t.getElementsByClassName(o.autosizesClass), c("resize", L)
                            },
                            checkElems: L,
                            updateElem: S
                        }),
                        M = function () {
                            !M.i && t.getElementsByClassName && (M.i = !0, R._(), N._())
                        };
                    var F, T, S, L;
                    var j, I, z, E, D, B, X, O, H, W, K, U, G, J, Y, V, Q, Z, ee, te, ne, ae, oe, ie, re, se, le, ce, ue, de, me, he, fe, pe;
                    var ge, be, ve, we, $e, ke, ye;
                    return u((function () {
                        o.init && M()
                    })), a = {
                        cfg: o,
                        autoSizer: R,
                        loader: N,
                        init: M,
                        uP: y,
                        aC: v,
                        rC: w,
                        hC: b,
                        fire: k,
                        gW: x,
                        rAF: _
                    }
                }(t, t.document, Date);
                t.lazySizes = a, e.exports && (e.exports = a)
            }("undefined" != typeof window ? window : {})
        }
    },
        t = {};

    function n(a) {
        var o = t[a];
        if (void 0 !== o) return o.exports;
        var i = t[a] = {
            exports: {}
        };
        return e[a](i, i.exports, n), i.exports
    }
    n.n = function (e) {
        var t = e && e.__esModule ? function () {
            return e.default
        } : function () {
            return e
        };
        return n.d(t, {
            a: t
        }), t
    }, n.d = function (e, t) {
        for (var a in t) n.o(t, a) && !n.o(e, a) && Object.defineProperty(e, a, {
            enumerable: !0,
            get: t[a]
        })
    }, n.o = function (e, t) {
        return Object.prototype.hasOwnProperty.call(e, t)
    },
        function () {
            "use strict";
            n(90);
            lazySizes.cfg.lazyClass = "lazy", lazySizes.cfg.loadMode = 1, n(946), n(438),
                function (e) {
                    if (e(document).on("lazyloaded", (function (t) {
                        e(t.target).next().hide();
                        var n = t.target;
                        n.complete && void 0 !== n.naturalWidth && 0 != n.naturalWidth || e(n).parents(".game-box").remove()
                    })), n(853), e("#carousel-fixed-height").on("slide.bs.carousel", (function (t) {
                        var n;
                        (n = e(t.relatedTarget).find("img[data-src]")) && n.length > 0 && (n.attr("src", n.data("src")), n.removeAttr("data-src"))
                    })), n(117), n(923), n(855), n(431), n(401), e(".footer-body").length) {
                        var t = e(".footer-body").offset().top + e(".footer-body").height(),
                            a = e(document).height();
                        e("#collapsible-footer").click((function (n) {
                            return e(".footer-title .i-collapse").toggleClass("rotate"), n.preventDefault(), e(".footer-body").slideToggle(), e("html, body").animate({
                                scrollTop: t + a
                            }, 2e3), !1
                        }))
                    }
                    n(928);
                    var o = !1;
                    try {
                        o = sessionStorage.getItem("isCloseAPKDownBar")
                    } catch (e) { }
                    if ("true" !== o && e("#apk-down-bar").show(), e("#btn-close--apk").click((function () {
                        e("#apk-down-bar").fadeToggle();
                        try {
                            sessionStorage.setItem("isCloseAPKDownBar", "true")
                        } catch (e) { }
                    })), document.getElementById("collapsible-footer") && (window.onscroll = function () {
                        var t;
                        t = e("#collapsible-footer").offset().top + e(window).height(), document.body.scrollTop > t || document.documentElement.scrollTop > t ? e("#btn-wrap--goToTop").show() : e("#btn-wrap--goToTop").hide()
                    }), window.topFunction = function () {
                        document.body.scrollTop = e("#collapsible-footer").offset().top - 150, document.documentElement.scrollTop = e("#collapsible-footer").offset().top - 150;
                        var t = e(".footer-body").offset().top + e(".footer-body").height(),
                            n = e(document).height();
                        e(".footer-body .i-collapse").toggleClass("rotate"), e(".footer-body").slideToggle(), e("html, body").animate({
                            scrollTop: t + n
                        }, 2e3)
                    }, e("#home_float-menu1").length > 0 && (e(document).click((function (t) {
                        !e(t.target).closest("#home_float-menu1").length && e("#home_float-menu1").hasClass("visible") && e("#home_float-menu1").removeClass("visible")
                    })), e("#home_float-menu1").click((function () {
                        e("#home_float-menu1").addClass("visible")
                    }))), window.isAuth) check_notification_status()
                }(jQuery), n(956)
        }()
}();