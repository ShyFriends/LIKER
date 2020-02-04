package com.example.likeair2020;

import android.app.Dialog;
import android.view.Window;
import android.widget.Button;
import android.widget.TextView;
import android.content.Context;
import android.view.View;
import android.os.Bundle;

public class Custom_dialog_welcome extends Dialog {

    private TextView Textview_welcome;
    private Button Button_getin;

    public interface Custom_dialog_welcome_eventlistener{
        public void custom_dialog_event(int getin);
    }

    private Custom_dialog_welcome_eventlistener custom_dialog_welcome_eventlistener;

    public Custom_dialog_welcome(Context context) {
        super(context);

        requestWindowFeature(Window.FEATURE_NO_TITLE);
        setContentView(R.layout.signin_custom_dialog_welcome);
        this.custom_dialog_welcome_eventlistener = custom_dialog_welcome_eventlistener;
    }

    Button btn_getin = (Button)findViewById(R.id.Button_getin);






}
