export type User = {
    // user info
    password: string;
    two_factor_verification: boolean;
    device_logs: [
        { device_name: string; ip_address: string; last_login_at: Date },
    ];
    personal_info: {
        first_name: string;
        last_name: string;
        display_name: string;
        email: string;
        ssn: string;
        date_of_birth: string;
        address_line1: string;
        address_line2: string;
        city: string;
        state: string;
        postal_code: number;
        country: string;
    };
    business_details: {
        first_name: string;
        last_name: string;
        legal_business_name: string;
        dba: string;
        entity_type: string;
        country: string;
        phone_number: string;
        verification_photo_id: string; // Driving license, State ID,passport, passport card, permanent resident card, non-citizen travel document, Visa, work permit
        business_in: string;
        industry: string;
        website: string;
        description: string;
        formation_date: Date;
        physical_address: string;
        legal_registered_address: string;
        title: string;
        control_person: boolean;
        beneficial_owner: boolean;
        percentage_owner_ship: string; // only for beneficial_owner
        controllers: [
            //if control_person false
            {
                first_name: string;
                last_name: string;
                job_title: string;
                email_address: string;
                is_individual_owner: boolean; // This individual is also an owner
                percentage_owner_ship: string;
            },
        ];
    };
    documents: {
        formation_document: string;
        ownership_document: string;
        principal_officer_id: string;
        supporting_documents: {
            type: string; // file types: llc_corporation_document, ein_letter, irs, any_business_proof, others
            file: string; // actual file
        };
    };
    billing_plan: any;
    accounts: Account[];
    payees: Payee[];
};

export type Account = {
    account_holder_name: string;
    account_nick_name: string;
    account_number: number;
    routing_number: number;
    phone_number: string;
    address_line1: string;
    address_line2: string;
    city: string;
    state: string;
    postal_code: number;
    country: string;
    email: string;
    next_check_starting_number: number;
    signature: string;
    ach_authorization_form: {
        account_number: number;
        authorizer: string; // Whom you authorize?
        full_name: string;
        title: string;
        digital_sign_name: string;
        digital_sign: string; // generated from digital_sign_name
    };
    address: Address;
};

// DONE
export type Plan = {};

export type Address = {};



export type Payee = {
    type: "customer" | "vendor" | "employee";
    payee_name: string;
    nick_name: string;
    email: string;
    phone_number: string;
    payee_id_account_number: number;
    entity_type: "individual" | "business";
    company_name: string; // if entity_type business
    address_line1: string;
    address_line2: string;
    country: string;
    state: string;
    city: string;
    postal_code: number;
    bank_account: {
        account_holder_name: string;
        routing_number: number;
        account_number: number;
        account_type: string;
    };
};



export type Payments = {
    account: Account;
    payee: Payee;
    logs: Log[];
    comments: Comment[];
    attachments: Attachment[];
    remittance: Remittance[];
    receipts: Receipts[];
    delivery_proofs: DeliveryProof[];
};

export type Receipts = {};
export type DeliveryProof = {};
export type Remittance = {};
export type Log = {};
export type Comment = {};
export type Attachment = {};