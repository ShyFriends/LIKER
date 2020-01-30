package com.example.likeair;

import androidx.appcompat.app.AppCompatActivity;

import android.os.Bundle;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;

public class MainActivity extends AppCompatActivity {

    Button Button_longin;

    EditText EditText_login, EditText_password;
    Button Button_login;
    TextView TextView_forgot, TextView_creat;


    TextView textView_LikeAir;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);


    }
}
